<?php

namespace App\Http\Controllers;

use App\Models\CricketMatch;
use App\Models\NewsArticle;
use App\Models\Player;
use App\Models\Team;
use App\Models\Transfer;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Display the league dashboard with headline statistics.
     */
    public function index(): View
    {
        $stats = [
            'teams' => Team::count(),
            'players' => Player::count(),
            'matches' => CricketMatch::count(),
            'transfers' => Transfer::count(),
            'active_players' => Player::where('is_active', true)->count(),
            'free_agents' => Player::whereNull('team_id')->count(),
            'confirmed_matches' => CricketMatch::where('status', 'confirmed')->count(),
            'pending_matches' => CricketMatch::whereIn('status', ['pending_review', 'pending_confirmation'])->count(),
        ];

        $recentMatches = CricketMatch::with(['homeTeam', 'awayTeam'])
            ->orderByDesc('match_date')
            ->limit(5)
            ->get();

        $topTeams = Team::withCount('players')
            ->with('manager')
            ->orderByDesc('budget')
            ->limit(5)
            ->get();

        $latestNews = NewsArticle::where('status', 'published')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $recentTransfers = Transfer::with(['player', 'fromTeam', 'toTeam'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'stats',
            'recentMatches',
            'topTeams',
            'latestNews',
            'recentTransfers',
        ));
    }
}
