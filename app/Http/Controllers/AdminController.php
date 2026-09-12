<?php

namespace App\Http\Controllers;

use App\Models\CricketMatch;
use App\Models\NewsArticle;
use App\Models\Player;
use App\Models\Setting;
use App\Models\Team;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard with platform statistics and settings.
     */
    public function index(): View
    {
        $stats = [
            'users' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'teams' => Team::count(),
            'players' => Player::count(),
            'matches' => CricketMatch::count(),
            'pending_matches' => CricketMatch::whereIn('status', ['pending_review', 'pending_confirmation'])->count(),
            'disputed_matches' => CricketMatch::where('status', 'disputed')->count(),
            'transfers' => Transfer::count(),
            'pending_transfers' => Transfer::where('status', 'pending')->count(),
            'news_drafts' => NewsArticle::where('status', 'draft')->count(),
            'total_budget' => Team::sum('budget'),
            'total_spent' => Team::sum('spent'),
            'total_squad_value' => Player::sum('current_value'),
        ];

        $settings = Setting::orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group');

        $recentMatches = CricketMatch::with(['homeTeam', 'awayTeam'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $pendingTransfers = Transfer::with(['player', 'fromTeam', 'toTeam'])
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('admin.index', compact('stats', 'settings', 'recentMatches', 'pendingTransfers'));
    }

    /**
     * Display the admin control panel for CRUD operations.
     */
    public function panel(): View
    {
        $stats = [
            'managers' => User::where('role', 'manager')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'teams' => Team::count(),
            'players' => Player::count(),
            'total_budget' => Team::sum('budget'),
        ];

        return view('admin.panel', compact('stats'));
    }
}
