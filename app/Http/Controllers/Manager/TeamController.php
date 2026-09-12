<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function show(Request $request): View
    {
        $team = Auth::user()->managedTeam;
        $sort = $request->input('sort', 'value_desc');
        $view = in_array($request->input('view'), ['table', 'compact'], true) ? $request->input('view') : 'grid';

        $players = collect();

        if ($team) {
            $query = $team->players();

            match ($sort) {
                'name_asc' => $query->orderBy('name'),
                'name_desc' => $query->orderByDesc('name'),
                'value_asc' => $query->orderBy('current_value'),
                'value_desc' => $query->orderByDesc('current_value'),
                'role' => $query->orderBy('role')->orderByDesc('current_value'),
                'tier' => $query->orderByRaw("FIELD(tier, 'Superstar', 'Star', 'Normal', 'Low-value')"),
                'age_asc' => $query->orderBy('age'),
                'age_desc' => $query->orderByDesc('age'),
                default => $query->orderByDesc('current_value'),
            };

            $players = $query->get();
        }

        return view('manager.team', compact('team', 'players', 'sort', 'view'));
    }
}
