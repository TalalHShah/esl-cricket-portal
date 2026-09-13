<?php

namespace App\Http\Controllers;

use App\Concerns\Sortable;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ValuationController extends Controller
{
    use Sortable;

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
    public function league(Request $request): View
    {
        $query = Player::with('team')
            ->where('is_active', true)
            ->select('id', 'name', 'role', 'tier', 'base_value', 'current_value', 'team_id');

        $sort = $this->applySort($query, $request, [
            'value_desc' => fn ($q) => $q->orderByDesc('current_value'),
            'value_asc' => fn ($q) => $q->orderBy('current_value'),
            'name_asc' => fn ($q) => $q->orderBy('name'),
            'change_desc' => fn ($q) => $q->orderByRaw('(current_value - base_value) desc'),
            'change_asc' => fn ($q) => $q->orderByRaw('(current_value - base_value) asc'),
            'tier' => fn ($q) => $q->orderByRaw("FIELD(tier, 'Platinum', 'Diamond', 'Gold', 'Silver')"),
        ], 'value_desc');

        $players = $query->paginate(20)->withQueryString();

        return view('valuations.league', compact('players', 'sort'));
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
