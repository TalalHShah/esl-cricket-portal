<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    /**
     * Display a listing of players with optional filters.
     */
    public function index(Request $request): View
    {
        $players = Player::with('team')
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')))
            ->when($request->filled('tier'), fn ($query) => $query->where('tier', $request->string('tier')))
            ->when($request->filled('team'), fn ($query) => $query->where('team_id', $request->integer('team')))
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%' . $request->string('search') . '%'))
            ->orderByDesc('current_value')
            ->paginate(20)
            ->withQueryString();

        $teams = Team::orderBy('name')->get(['id', 'name']);

        return view('players.index', compact('players', 'teams'));
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
