<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Team;
use App\Services\CompetitionBracketService;
use App\Services\CompetitionFixtureService;
use App\Services\CompetitionStandingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompetitionController extends Controller
{
    public function index(): View
    {
        $competitions = Competition::withCount('teams')->orderByDesc('created_at')->get();

        return view('admin.competitions.index', compact('competitions'));
    }

    public function create(): View
    {
        return view('admin.competitions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $competition = Competition::create($validated);

        return redirect()->route('admin.competitions.show', $competition)->with('status', "{$competition->name} created — add teams, then generate fixtures.");
    }

    public function show(Competition $competition, CompetitionStandingsService $standingsService): View
    {
        $competition->load(['teams', 'matches' => fn ($q) => $q->with(['homeTeam', 'awayTeam', 'winnerTeam'])->orderBy('leg')->orderBy('match_date')]);

        $allTeams = Team::orderBy('name')->get();
        $standings = $competition->isLeague() ? $standingsService->standings($competition) : collect();

        $leagueMatches = $competition->matches->where('stage', Competition::STAGE_LEAGUE);
        $playoffMatches = $competition->matches->whereIn('stage', [
            Competition::STAGE_PLAYOFF_1, Competition::STAGE_PLAYOFF_2, Competition::STAGE_QUALIFIER_2, Competition::STAGE_FINAL,
        ]);

        $leagueStageComplete = $competition->isLeague()
            && $leagueMatches->isNotEmpty()
            && $leagueMatches->every(fn ($m) => $m->status === 'confirmed');

        return view('admin.competitions.show', compact(
            'competition', 'allTeams', 'standings', 'leagueMatches', 'playoffMatches', 'leagueStageComplete'
        ));
    }

    public function edit(Competition $competition): View
    {
        return view('admin.competitions.edit', compact('competition'));
    }

    public function update(Request $request, Competition $competition): RedirectResponse
    {
        $competition->update($this->validated($request, $competition));

        return redirect()->route('admin.competitions.show', $competition)->with('status', 'Competition settings updated.');
    }

    public function destroy(Competition $competition): RedirectResponse
    {
        if ($competition->matches()->where('status', 'confirmed')->exists()) {
            return back()->withErrors(['competition' => 'This competition has confirmed results and cannot be deleted.']);
        }

        $competition->matches()->delete();
        $competition->delete();

        return redirect()->route('admin.competitions.index')->with('status', 'Competition deleted.');
    }

    public function addTeam(Request $request, Competition $competition): RedirectResponse
    {
        $validated = $request->validate(['team_id' => 'required|exists:teams,id']);

        $competition->teams()->syncWithoutDetaching([$validated['team_id']]);

        return back()->with('status', 'Team added.');
    }

    public function removeTeam(Competition $competition, Team $team): RedirectResponse
    {
        if ($competition->matches()->exists()) {
            return back()->withErrors(['competition' => 'Fixtures already exist — remove the team before generating fixtures, not after.']);
        }

        $competition->teams()->detach($team->id);

        return back()->with('status', 'Team removed.');
    }

    public function generateFixtures(Competition $competition, CompetitionFixtureService $service): RedirectResponse
    {
        try {
            $count = $service->generateLeagueFixtures($competition);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['competition' => $e->getMessage()]);
        }

        return back()->with('status', "{$count} league fixtures generated.");
    }

    public function generatePlayoffs(Competition $competition, CompetitionBracketService $service): RedirectResponse
    {
        $result = $service->generatePlayoffs($competition);

        if (isset($result['error'])) {
            return back()->withErrors(['competition' => $result['error']]);
        }

        return back()->with('status', 'Playoff 1 and Playoff 2 fixtures created.');
    }

    private function validated(Request $request, ?Competition $competition = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:league,cup',
            'description' => 'nullable|string|max:2000',
            'rounds' => 'required|integer|in:1,2',
            'total_overs' => 'required|integer|min:1|max:50',
            'points_win' => 'required|integer|min:0|max:10',
            'points_tie' => 'required|integer|min:0|max:10',
            'points_loss' => 'required|integer|min:0|max:10',
            'starts_on' => 'nullable|date',
            'fixture_interval_days' => 'required|integer|min:1|max:30',
        ]);

        $validated['has_playoffs'] = $request->boolean('has_playoffs');
        $validated['is_official'] = $request->boolean('is_official', true);

        return $validated;
    }
}
