<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Contracts\View\View;

class TeamController extends Controller
{
    /**
     * Display a listing of all teams.
     */
    public function index(): View
    {
        $teams = Team::with('manager')
            ->withCount('players')
            ->orderBy('name')
            ->paginate(12);

        return view('teams.index', compact('teams'));
    }

    /**
     * Display a single team along with its squad.
     */
    public function show(Team $team): View
    {
        $team->load('manager');

        $players = $team->players()
            ->orderByDesc('current_value')
            ->get();

        $matches = $team->homeMatches()
            ->with(['homeTeam', 'awayTeam'])
            ->orWhere('away_team_id', $team->id)
            ->orderByDesc('match_date')
            ->limit(10)
            ->get();

        return view('teams.show', compact('team', 'players', 'matches'));
    }
}
