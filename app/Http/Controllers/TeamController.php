<?php

namespace App\Http\Controllers;

use App\Concerns\Sortable;
use App\Models\Team;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    use Sortable;

    /**
     * Display a listing of all teams.
     */
    public function index(Request $request): View
    {
        $query = Team::with('manager')->withCount('players');

        $sort = $this->applySort($query, $request, [
            'name_asc' => fn ($q) => $q->orderBy('name'),
            'name_desc' => fn ($q) => $q->orderByDesc('name'),
            'budget_desc' => fn ($q) => $q->orderByDesc('budget'),
            'budget_asc' => fn ($q) => $q->orderBy('budget'),
            'remaining_desc' => fn ($q) => $q->orderByRaw('(budget - spent) desc'),
            'players_desc' => fn ($q) => $q->orderByDesc('players_count'),
        ], 'name_asc');

        $teams = $query->paginate(12)->withQueryString();

        return view('teams.index', compact('teams', 'sort'));
    }

    /**
     * Display a single team along with its squad.
     */
    public function show(Request $request, Team $team): View
    {
        $team->load('manager');

        $query = $team->players();

        $sort = $this->applySort($query, $request, [
            'value_desc' => fn ($q) => $q->orderByDesc('current_value'),
            'value_asc' => fn ($q) => $q->orderBy('current_value'),
            'name_asc' => fn ($q) => $q->orderBy('name'),
            'name_desc' => fn ($q) => $q->orderByDesc('name'),
            'role' => fn ($q) => $q->orderBy('role')->orderByDesc('current_value'),
            'tier' => fn ($q) => $q->orderByRaw("FIELD(tier, 'Superstar', 'Star', 'Normal', 'Low-value')"),
            'age_asc' => fn ($q) => $q->orderBy('age'),
            'age_desc' => fn ($q) => $q->orderByDesc('age'),
        ], 'value_desc');

        $players = $query->get();

        $matches = $team->homeMatches()
            ->with(['homeTeam', 'awayTeam'])
            ->orWhere('away_team_id', $team->id)
            ->orderByDesc('match_date')
            ->limit(10)
            ->get();

        return view('teams.show', compact('team', 'players', 'matches', 'sort'));
    }
}
