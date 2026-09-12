<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\Sortable;
use App\Http\Controllers\Controller;
use App\Models\CricketMatch;
use App\Models\MatchScreenshot;
use App\Models\MatchStat;
use App\Models\Team;
use App\Services\PlayerValuationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminMatchController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $query = CricketMatch::with(['homeTeam', 'awayTeam', 'winnerTeam']);

        $sort = $this->applySort($query, $request, [
            'date_desc' => fn ($q) => $q->orderByDesc('match_date'),
            'date_asc' => fn ($q) => $q->orderBy('match_date'),
            'status' => fn ($q) => $q->orderBy('status'),
        ], 'date_desc');

        $matches = $query->paginate(15)->withQueryString();

        return view('admin.matches.index', compact('matches', 'sort'));
    }

    public function create(): View
    {
        $teams = Team::orderBy('name')->get();

        return view('admin.matches.create', compact('teams'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'home_team_id' => 'required|exists:teams,id|different:away_team_id',
            'away_team_id' => 'required|exists:teams,id',
            'match_date' => 'required|date',
        ]);

        $match = CricketMatch::create([
            ...$validated,
            'status' => 'pending_review',
            'submitted_by_user_id' => Auth::id(),
        ]);

        return redirect()->route('admin.matches.edit', $match)->with('status', 'Match scheduled. Add stats and confirm the result once it is played.');
    }

    public function edit(CricketMatch $match): View
    {
        $match->load(['homeTeam.players', 'awayTeam.players', 'stats', 'screenshots']);

        $statsByPlayer = $match->stats->keyBy('player_id');

        return view('admin.matches.edit', compact('match', 'statsByPlayer'));
    }

    public function update(Request $request, CricketMatch $match): RedirectResponse
    {
        if ($match->status === 'confirmed') {
            return back()->withErrors(['match' => 'A confirmed match cannot be edited. Cancel and reschedule if this was recorded in error.']);
        }

        $validated = $request->validate([
            'match_date' => 'required|date',
            'winner_team_id' => 'nullable|exists:teams,id',
            'status' => 'required|in:pending_review,pending_confirmation,disputed',
            'summary_notes' => 'nullable|string|max:2000',
            'stats' => 'array',
            'stats.*.runs_scored' => 'nullable|integer|min:0',
            'stats.*.balls_faced' => 'nullable|integer|min:0',
            'stats.*.fours' => 'nullable|integer|min:0',
            'stats.*.sixes' => 'nullable|integer|min:0',
            'stats.*.overs_bowled' => 'nullable|numeric|min:0',
            'stats.*.maidens' => 'nullable|integer|min:0',
            'stats.*.runs_conceded' => 'nullable|integer|min:0',
            'stats.*.wickets_taken' => 'nullable|integer|min:0',
            'stats.*.catches' => 'nullable|integer|min:0',
            'stats.*.stumpings' => 'nullable|integer|min:0',
        ]);

        if ($validated['winner_team_id'] ?? null) {
            if (! in_array((int) $validated['winner_team_id'], [$match->home_team_id, $match->away_team_id], true)) {
                return back()->withErrors(['winner_team_id' => 'The winner must be one of the two competing teams.']);
            }
        }

        DB::transaction(function () use ($validated, $match) {
            $match->update([
                'match_date' => $validated['match_date'],
                'winner_team_id' => $validated['winner_team_id'] ?? null,
                'status' => $validated['status'],
                'summary_notes' => $validated['summary_notes'] ?? null,
            ]);

            foreach ($validated['stats'] ?? [] as $playerId => $row) {
                $hasAnyValue = collect($row)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                if (! $hasAnyValue) {
                    continue;
                }

                $player = $match->homeTeam->players->firstWhere('id', $playerId)
                    ?? $match->awayTeam->players->firstWhere('id', $playerId);

                if (! $player) {
                    continue;
                }

                MatchStat::updateOrCreate(
                    ['match_id' => $match->id, 'player_id' => $playerId],
                    [
                        'team_id' => $player->team_id,
                        'runs_scored' => $row['runs_scored'] ?? 0,
                        'balls_faced' => $row['balls_faced'] ?? 0,
                        'fours' => $row['fours'] ?? 0,
                        'sixes' => $row['sixes'] ?? 0,
                        'overs_bowled' => $row['overs_bowled'] ?? 0,
                        'maidens' => $row['maidens'] ?? 0,
                        'runs_conceded' => $row['runs_conceded'] ?? 0,
                        'wickets_taken' => $row['wickets_taken'] ?? 0,
                        'catches' => $row['catches'] ?? 0,
                        'stumpings' => $row['stumpings'] ?? 0,
                    ]
                );
            }
        });

        return redirect()->route('admin.matches.edit', $match)->with('status', 'Match updated.');
    }

    public function confirm(CricketMatch $match): RedirectResponse
    {
        if (! $match->winner_team_id) {
            return back()->withErrors(['match' => 'Set a winning team before confirming the result.']);
        }

        if ($match->status === 'confirmed') {
            return back()->withErrors(['match' => 'This match is already confirmed.']);
        }

        DB::transaction(function () use ($match) {
            $match->update([
                'status' => 'confirmed',
                'confirmed_by_user_id' => Auth::id(),
            ]);

            app(PlayerValuationService::class)->updateValuationsForMatch($match->fresh());
        });

        return redirect()->route('admin.matches.index')->with('status', 'Match confirmed — player valuations have been updated.');
    }

    public function destroy(CricketMatch $match): RedirectResponse
    {
        if ($match->status === 'confirmed') {
            return back()->withErrors(['match' => 'A confirmed match cannot be deleted.']);
        }

        $match->delete();

        return redirect()->route('admin.matches.index')->with('status', 'Match removed.');
    }

    public function uploadScreenshot(Request $request, CricketMatch $match): RedirectResponse
    {
        $validated = $request->validate([
            'screenshot' => 'required|image|max:8192',
            'type' => 'required|in:scorecard,result,dispute,other',
            'caption' => 'nullable|string|max:255',
        ]);

        $path = $request->file('screenshot')->store('match-screenshots', 'public');

        MatchScreenshot::create([
            'match_id' => $match->id,
            'uploaded_by_user_id' => Auth::id(),
            'file_path' => $path,
            'original_name' => $request->file('screenshot')->getClientOriginalName(),
            'mime_type' => $request->file('screenshot')->getMimeType(),
            'file_size' => $request->file('screenshot')->getSize(),
            'type' => $validated['type'],
            'caption' => $validated['caption'] ?? null,
        ]);

        return back()->with('status', 'Screenshot uploaded.');
    }

    public function destroyScreenshot(CricketMatch $match, MatchScreenshot $screenshot): RedirectResponse
    {
        if ($screenshot->match_id !== $match->id) {
            abort(404);
        }

        Storage::disk('public')->delete($screenshot->file_path);
        $screenshot->delete();

        return back()->with('status', 'Screenshot removed.');
    }
}
