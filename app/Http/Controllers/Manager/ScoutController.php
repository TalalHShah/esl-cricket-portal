<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Transfer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScoutController extends Controller
{
    public function index(Request $request): View
    {
        $ownTeam = Auth::user()->managedTeam;

        $query = Player::query()->with('team')->where('is_active', true);

        if ($ownTeam) {
            $query->where(function ($q) use ($ownTeam) {
                $q->whereNull('team_id')->orWhere('team_id', '!=', $ownTeam->id);
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('tier')) {
            $query->where('tier', $request->input('tier'));
        }

        if ($request->filled('country')) {
            $query->where('country', 'like', '%' . $request->input('country') . '%');
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->input('availability') === 'free') {
            $query->whereNull('team_id');
        } elseif ($request->input('availability') === 'signed') {
            $query->whereNotNull('team_id');
        }

        $players = $query->orderByRaw('team_id IS NULL DESC')->orderByDesc('current_value')->paginate(12)->withQueryString();

        $roles = ['Batsman', 'Wicketkeeper', 'All-rounder', 'Fast Bowler', 'Spinner'];
        $tiers = ['Superstar', 'Star', 'Normal', 'Low-value'];

        return view('manager.scouts', compact('players', 'roles', 'tiers'));
    }

    public function sign(Player $player): RedirectResponse
    {
        $team = Auth::user()->managedTeam;

        if (! $team) {
            return back()->withErrors(['sign' => 'You are not assigned to manage a team.']);
        }

        if ($player->team_id !== null) {
            return back()->withErrors(['sign' => 'This player is already signed to a team.']);
        }

        $fee = (float) $player->current_value;
        $remainingBudget = $team->budget - $team->spent;

        if ($remainingBudget < $fee) {
            return back()->withErrors(['sign' => 'Insufficient budget to sign this player.']);
        }

        DB::transaction(function () use ($player, $team, $fee) {
            $player->update([
                'team_id' => $team->id,
                'sold_price' => $fee,
            ]);

            $team->increment('spent', $fee);

            Transfer::create([
                'player_id' => $player->id,
                'from_team_id' => null,
                'to_team_id' => $team->id,
                'fee' => $fee,
                'type' => 'direct',
                'status' => 'approved',
                'requested_by_user_id' => Auth::id(),
                'approved_by_user_id' => Auth::id(),
                'effective_at' => now(),
                'notes' => "Free agent signing: {$player->name}",
            ]);
        });

        return back()->with('status', "{$player->name} signed for PKR " . number_format($fee, 0) . '.');
    }
}
