<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Services\CompetitionStandingsService;
use Illuminate\Contracts\View\View;

class CompetitionController extends Controller
{
    public function index(): View
    {
        $competitions = Competition::withCount('teams')
            ->orderByRaw("FIELD(status, 'playoffs', 'league_stage', 'draft', 'completed')")
            ->orderByDesc('created_at')
            ->get();

        return view('competitions.index', compact('competitions'));
    }

    public function show(Competition $competition, CompetitionStandingsService $standingsService): View
    {
        $competition->load(['teams', 'matches' => fn ($q) => $q->with(['homeTeam', 'awayTeam', 'winnerTeam'])->orderBy('leg')->orderBy('match_date')]);

        $standings = $competition->isLeague() ? $standingsService->standings($competition) : collect();

        $leagueMatches = $competition->matches->where('stage', Competition::STAGE_LEAGUE);
        $playoffMatches = $competition->matches->whereIn('stage', [
            Competition::STAGE_PLAYOFF_1, Competition::STAGE_PLAYOFF_2, Competition::STAGE_QUALIFIER_2, Competition::STAGE_FINAL,
        ]);
        $cupMatches = $competition->isCup() ? $competition->matches : collect();

        return view('competitions.show', compact('competition', 'standings', 'leagueMatches', 'playoffMatches', 'cupMatches'));
    }
}
