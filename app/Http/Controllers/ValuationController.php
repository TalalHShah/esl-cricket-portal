<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\View\View;

class ValuationController extends Controller
{
    /**
     * Show player valuations for a team.
     */
    public function byTeam($teamId): View
    {
        $team = \App\Models\Team::findOrFail($teamId);
        $players = $team->players()
            ->select('id', 'name', 'role', 'tier', 'base_value', 'current_value', 'real_life_form_modifier')
            ->orderByDesc('current_value')
            ->get();

        $totalBaseValue = $players->sum('base_value');
        $totalCurrentValue = $players->sum('current_value');
        $gainLoss = $totalCurrentValue - $totalBaseValue;

        return view('valuations.team', compact('team', 'players', 'totalBaseValue', 'totalCurrentValue', 'gainLoss'));
    }

    /**
     * Show all player valuations across league.
     */
    public function league(): View
    {
        $players = Player::with('team')
            ->where('is_active', true)
            ->select('id', 'name', 'role', 'tier', 'base_value', 'current_value', 'team_id')
            ->orderByDesc('current_value')
            ->paginate(20);

        return view('valuations.league', compact('players'));
    }

    /**
     * Show tier breakdown (how many in each tier).
     */
    public function tiers(): View
    {
        $tiers = Player::where('is_active', true)
            ->selectRaw('tier, COUNT(*) as count, AVG(current_value) as avg_value')
            ->groupBy('tier')
            ->get();

        return view('valuations.tiers', compact('tiers'));
    }
}
