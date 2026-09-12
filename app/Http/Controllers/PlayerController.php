<?php

namespace App\Http\Controllers;

use App\Concerns\Sortable;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    use Sortable;

    /**
     * Display a listing of players with optional filters.
     */
    public function index(Request $request): View
    {
        $query = Player::with('team')
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))
            ->when($request->filled('tier'), fn ($q) => $q->where('tier', $request->string('tier')))
            ->when($request->filled('team'), fn ($q) => $q->where('team_id', $request->integer('team')))
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->string('search') . '%'));

        $sort = $this->applySort($query, $request, [
            'value_desc' => fn ($q) => $q->orderByDesc('current_value'),
            'value_asc' => fn ($q) => $q->orderBy('current_value'),
            'name_asc' => fn ($q) => $q->orderBy('name'),
            'name_desc' => fn ($q) => $q->orderByDesc('name'),
            'role' => fn ($q) => $q->orderBy('role')->orderByDesc('current_value'),
            'tier' => fn ($q) => $q->orderByRaw("FIELD(tier, 'Superstar', 'Star', 'Normal', 'Low-value')"),
            'age_asc' => fn ($q) => $q->orderBy('age'),
            'age_desc' => fn ($q) => $q->orderByDesc('age'),
            'country_asc' => fn ($q) => $q->orderBy('country'),
        ], 'value_desc');

        $players = $query->paginate(20)->withQueryString();

        $teams = Team::orderBy('name')->get(['id', 'name']);

        return view('players.index', compact('players', 'teams', 'sort'));
    }

    /**
     * Display a single player and their match statistics.
     */
    public function show(Player $player): View
    {
        $player->load('team');

        $stats = $player->matchStats()
            ->with(['match.homeTeam', 'match.awayTeam'])
            ->orderByDesc('created_at')
            ->get();

        $totals = [
            'matches' => $stats->count(),
            'runs' => $stats->sum('runs_scored'),
            'balls' => $stats->sum('balls_faced'),
            'fours' => $stats->sum('fours'),
            'sixes' => $stats->sum('sixes'),
            'wickets' => $stats->sum('wickets_taken'),
            'overs' => $stats->sum('overs_bowled'),
            'catches' => $stats->sum('catches'),
            'stumpings' => $stats->sum('stumpings'),
        ];

        $transfers = $player->transfers()
            ->with(['fromTeam', 'toTeam'])
            ->orderByDesc('created_at')
            ->get();

        return view('players.show', compact('player', 'stats', 'totals', 'transfers'));
    }
}
