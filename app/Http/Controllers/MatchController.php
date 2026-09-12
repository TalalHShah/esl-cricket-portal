<?php

namespace App\Http\Controllers;

use App\Models\CricketMatch;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    /**
     * Display a listing of matches with optional status filter.
     */
    public function index(Request $request): View
    {
        $matches = CricketMatch::with(['homeTeam', 'awayTeam', 'winnerTeam'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderByDesc('match_date')
            ->paginate(15)
            ->withQueryString();

        return view('matches.index', compact('matches'));
    }

    /**
     * Display a single match with its per-player statistics.
     */
    public function show(CricketMatch $match): View
    {
        $match->load([
            'homeTeam',
            'awayTeam',
            'winnerTeam',
            'submittedBy',
            'confirmedBy',
            'screenshots',
        ]);

        $stats = $match->stats()
            ->with(['player', 'team'])
            ->get();

        $homeStats = $stats->where('team_id', $match->home_team_id)
            ->sortByDesc('runs_scored')
            ->values();

        $awayStats = $stats->where('team_id', $match->away_team_id)
            ->sortByDesc('runs_scored')
            ->values();

        return view('matches.show', compact('match', 'stats', 'homeStats', 'awayStats'));
    }
}
