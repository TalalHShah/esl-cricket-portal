<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\CricketMatch;
use App\Models\Transfer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $team = $user->managedTeam;

        $upcomingMatches = collect();
        $recentTransfers = collect();
        $squadCount = 0;
        $topPlayers = collect();

        if ($team) {
            $upcomingMatches = CricketMatch::with(['homeTeam', 'awayTeam'])
                ->where(function ($q) use ($team) {
                    $q->where('home_team_id', $team->id)->orWhere('away_team_id', $team->id);
                })
                ->orderByDesc('match_date')
                ->limit(5)
                ->get();

            $recentTransfers = Transfer::with(['player', 'fromTeam', 'toTeam'])
                ->where('from_team_id', $team->id)
                ->orWhere('to_team_id', $team->id)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            $squadCount = $team->players()->count();

            $topPlayers = $team->players()
                ->orderByDesc('current_value')
                ->limit(5)
                ->get();
        }

        return view('manager.dashboard', compact('team', 'upcomingMatches', 'recentTransfers', 'squadCount', 'topPlayers'));
    }
}
