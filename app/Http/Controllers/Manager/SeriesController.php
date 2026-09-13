<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\CricketMatch;
use App\Models\Series;
use App\Models\SeriesTeam;
use App\Models\Team;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Manager-run bilateral series and tri-series — separate from the
 * admin's official round-robin Competitions. A manager names the
 * series, picks one opponent (bilateral) or two (tri-series) and a
 * default venue; invited teams accept or decline. Once every team has
 * accepted, the series is 'active' and any participant (or an admin)
 * can add fixtures to it by hand, each with its own format
 * (T10/T20/ODI/Test) and venue. Fixtures created this way are ordinary
 * matches — they go through the same scorecard/confirm/valuation
 * pipeline as official matches.
 */
class SeriesController extends Controller
{
    public function index(): View
    {
        $team = Auth::user()->managedTeam;

        $series = $team
            ? Series::whereHas('seriesTeams', fn ($q) => $q->where('team_id', $team->id))
                ->orWhere('created_by_team_id', $team->id)
                ->with(['teams', 'createdByTeam'])
                ->orderByDesc('created_at')
                ->get()
            : collect();

        $teams = Team::when($team, fn ($q) => $q->where('id', '!=', $team->id))->orderBy('name')->get();

        return view('manager.series.index', compact('series', 'team', 'teams'));
    }

    public function create(): View
    {
        $team = Auth::user()->managedTeam;
        $teams = Team::when($team, fn ($q) => $q->where('id', '!=', $team->id))->orderBy('name')->get();

        return view('manager.series.create', compact('teams'));
    }

    public function store(Request $request): RedirectResponse
    {
        $team = Auth::user()->managedTeam;

        if (! $team && ! Auth::user()->isAdmin()) {
            return back()->withErrors(['series' => 'You are not assigned to manage a team.']);
        }

        $request->merge([
            'opponent_team_ids' => array_values(array_filter((array) $request->input('opponent_team_ids', []), fn ($id) => $id !== null && $id !== '')),
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'venue' => 'nullable|string|max:255',
            'opponent_team_ids' => 'required|array|min:1|max:2',
            'opponent_team_ids.*' => 'required|exists:teams,id|distinct',
        ]);

        if ($team && in_array($team->id, $validated['opponent_team_ids'])) {
            return back()->withErrors(['opponent_team_ids' => 'You cannot invite your own team.'])->withInput();
        }

        // An admin without a managed team creates the series on behalf
        // of the first opponent picked, who is auto-accepted as host.
        $hostTeamId = $team?->id ?? $validated['opponent_team_ids'][0];
        $inviteeIds = $team ? $validated['opponent_team_ids'] : array_slice($validated['opponent_team_ids'], 1);

        $series = DB::transaction(function () use ($validated, $hostTeamId, $inviteeIds) {
            $series = Series::create([
                'name' => $validated['name'],
                'venue' => $validated['venue'] ?? null,
                'status' => 'pending_invites',
                'created_by_team_id' => $hostTeamId,
                'created_by_user_id' => Auth::id(),
            ]);

            SeriesTeam::create([
                'series_id' => $series->id,
                'team_id' => $hostTeamId,
                'status' => 'accepted',
                'responded_at' => now(),
            ]);

            foreach ($inviteeIds as $teamId) {
                SeriesTeam::create([
                    'series_id' => $series->id,
                    'team_id' => $teamId,
                    'status' => 'invited',
                ]);
            }

            return $series;
        });

        return redirect()->route('manager.series.show', $series)->with('status', "'{$series->name}' created — waiting on the invited team(s) to respond.");
    }

    public function show(Series $series): View
    {
        $team = Auth::user()->managedTeam;
        $this->authorizeParty($series, $team);

        $series->load(['teams', 'createdByTeam', 'matches.homeTeam', 'matches.awayTeam', 'matches.winnerTeam']);

        $myInvite = $team ? $series->seriesTeams()->where('team_id', $team->id)->first() : null;
        $canManage = $series->status === 'active' && ($team && $series->teams->contains('id', $team->id) || Auth::user()->isAdmin());

        return view('manager.series.show', compact('series', 'team', 'myInvite', 'canManage'));
    }

    public function respond(Request $request, Series $series): RedirectResponse
    {
        $team = Auth::user()->managedTeam;
        $this->authorizeParty($series, $team);

        $validated = $request->validate(['response' => 'required|in:accept,decline']);

        $invite = $team ? $series->seriesTeams()->where('team_id', $team->id)->first() : null;

        if (! $invite) {
            return back()->withErrors(['series' => 'You are not invited to this series.']);
        }

        if ($invite->status !== 'invited') {
            return back()->withErrors(['series' => 'You have already responded to this invitation.']);
        }

        DB::transaction(function () use ($series, $invite, $validated) {
            $invite->update([
                'status' => $validated['response'] === 'accept' ? 'accepted' : 'declined',
                'responded_at' => now(),
            ]);

            $series->refresh();

            if ($validated['response'] === 'decline') {
                $series->update(['status' => 'cancelled', 'ended_at' => now()]);
            } elseif ($series->allTeamsAccepted()) {
                $series->update(['status' => 'active']);
            }
        });

        return redirect()->route('manager.series.show', $series)->with('status', $validated['response'] === 'accept' ? 'Invitation accepted.' : 'Invitation declined — the series has been cancelled.');
    }

    public function storeFixture(Request $request, Series $series): RedirectResponse
    {
        $team = Auth::user()->managedTeam;
        $this->authorizeParty($series, $team);

        if ($series->status !== 'active') {
            return back()->withErrors(['fixture' => 'Fixtures can only be added once every invited team has accepted.']);
        }

        $participantIds = $series->teams->pluck('id')->all();

        $validated = $request->validate([
            'home_team_id' => ['required', 'different:away_team_id', function ($attr, $value, $fail) use ($participantIds) {
                if (! in_array((int) $value, $participantIds, true)) {
                    $fail('The home team must be part of this series.');
                }
            }],
            'away_team_id' => ['required', function ($attr, $value, $fail) use ($participantIds) {
                if (! in_array((int) $value, $participantIds, true)) {
                    $fail('The away team must be part of this series.');
                }
            }],
            'match_date' => 'required|date',
            'match_format' => 'required|in:T10,T20,ODI,Test',
            'venue' => 'nullable|string|max:255',
        ]);

        CricketMatch::create([
            ...$validated,
            'series_id' => $series->id,
            'venue' => $validated['venue'] ?? $series->venue,
            'status' => 'pending_review',
            'submitted_by_user_id' => Auth::id(),
        ]);

        return redirect()->route('manager.series.show', $series)->with('status', 'Fixture added.');
    }

    public function cancel(Series $series): RedirectResponse
    {
        $team = Auth::user()->managedTeam;
        $this->authorizeParty($series, $team);

        $isCreator = $team && $series->created_by_team_id === $team->id;

        if (! $isCreator && ! Auth::user()->isAdmin()) {
            return back()->withErrors(['series' => 'Only the series creator or an admin can cancel it.']);
        }

        if (in_array($series->status, ['completed', 'cancelled'], true)) {
            return back()->withErrors(['series' => 'This series has already concluded.']);
        }

        if ($series->matches()->where('status', 'confirmed')->exists()) {
            return back()->withErrors(['series' => 'This series has confirmed results and cannot be cancelled — those matches stand on the record.']);
        }

        $series->update(['status' => 'cancelled', 'ended_at' => now()]);

        return redirect()->route('manager.series.index')->with('status', "'{$series->name}' cancelled.");
    }

    private function authorizeParty(Series $series, ?Team $team): void
    {
        if (Auth::user()->isAdmin()) {
            return;
        }

        abort_if(! $team || ! $series->teams->contains('id', $team->id), 403);
    }
}
