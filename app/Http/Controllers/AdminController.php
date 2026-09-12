<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AdminController extends Controller
{
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
