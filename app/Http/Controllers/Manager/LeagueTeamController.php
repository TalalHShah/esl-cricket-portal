<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeagueTeamController extends Controller
{
    public function index(): View
    {
        $ownTeam = Auth::user()->managedTeam;

        $teams = Team::withCount('players')
            ->with('manager')
            ->orderBy('name')
            ->get();

        return view('manager.league-teams', compact('teams', 'ownTeam'));
    }

    public function show(Request $request, Team $team): View
    {
        $sort = $request->input('sort', 'value_desc');
        $view = in_array($request->input('view'), ['table', 'compact'], true) ? $request->input('view') : 'grid';

        $query = $team->players()->with('team');

        match ($sort) {
            'name_asc' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            'value_asc' => $query->orderBy('current_value'),
            'value_desc' => $query->orderByDesc('current_value'),
            'role' => $query->orderBy('role')->orderByDesc('current_value'),
            'tier' => $query->orderByRaw("FIELD(tier, 'Platinum', 'Diamond', 'Gold', 'Silver')"),
            'age_asc' => $query->orderBy('age'),
            'age_desc' => $query->orderByDesc('age'),
            default => $query->orderByDesc('current_value'),
        };

        $players = $query->get();
        $ownTeam = Auth::user()->managedTeam;

        return view('manager.league-team-show', compact('team', 'players', 'sort', 'view', 'ownTeam'));
    }
}
