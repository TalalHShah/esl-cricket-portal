<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\CricketMatch;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class FixtureController extends Controller
{
    public function index(): View
    {
        $team = Auth::user()->managedTeam;

        $matches = collect();

        if ($team) {
            $matches = CricketMatch::with(['homeTeam', 'awayTeam'])
                ->where(function ($q) use ($team) {
                    $q->where('home_team_id', $team->id)->orWhere('away_team_id', $team->id);
                })
                ->orderByDesc('match_date')
                ->paginate(10);
        }

        return view('manager.fixtures', compact('team', 'matches'));
    }
}
