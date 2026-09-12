<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function show(): View
    {
        $team = Auth::user()->managedTeam;

        $players = $team ? $team->players()->orderByDesc('current_value')->get() : collect();

        return view('manager.team', compact('team', 'players'));
    }
}
