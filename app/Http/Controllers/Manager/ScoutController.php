<?php

namespace App\Http\Controllers\Manager;

use App\Concerns\Sortable;
use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Setting;
use App\Models\Transfer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScoutController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $ownTeam = Auth::user()->managedTeam;
        $windowOpen = Setting::getValue('transfer_window.open', true);

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

        $sort = $this->applySort($query, $request, [
            'default' => fn ($q) => $q->orderByRaw('team_id IS NULL DESC')->orderByDesc('current_value'),
            'value_desc' => fn ($q) => $q->orderByDesc('current_value'),
            'value_asc' => fn ($q) => $q->orderBy('current_value'),
            'name_asc' => fn ($q) => $q->orderBy('name'),
            'name_desc' => fn ($q) => $q->orderByDesc('name'),
            'role' => fn ($q) => $q->orderBy('role')->orderByDesc('current_value'),
            'tier' => fn ($q) => $q->orderByRaw("FIELD(tier, 'Superstar', 'Star', 'Normal', 'Low-value')"),
            'age_asc' => fn ($q) => $q->orderBy('age'),
            'age_desc' => fn ($q) => $q->orderByDesc('age'),
            'country_asc' => fn ($q) => $q->orderBy('country'),
        ], 'default');

        $players = $query->paginate(12)->withQueryString();

        $roles = ['Batsman', 'All-rounder', 'Bowler', 'Wicketkeeper'];
        $tiers = ['Superstar', 'Star', 'Normal', 'Low-value'];

        return view('manager.scouts', compact('players', 'roles', 'tiers', 'windowOpen', 'sort'));
    }

    public function sign(Player $player): RedirectResponse
    {
        $team = Auth::user()->managedTeam;

        if (! $team) {
            return back()->withErrors(['sign' => 'You are not assigned to manage a team.']);
        }

        if (! Setting::getValue('transfer_window.open', true)) {
            return back()->withErrors(['sign' => 'The transfer window is currently closed. Free agents are being signed through the Auction instead.']);
        }

        $result = DB::transaction(function () use ($player, $team) {
            $lockedPlayer = Player::whereKey($player->id)->lockForUpdate()->first();

            if ($lockedPlayer->team_id !== null) {
                return ['error' => 'This player is already signed to a team.'];
            }

            $fee = (float) $lockedPlayer->current_value;
            $lockedTeam = $team->fresh();
            $remainingBudget = $lockedTeam->remainingBudget();

            if ($remainingBudget < $fee) {
                return ['error' => 'Insufficient budget to sign this player.'];
            }

            $updated = Player::whereKey($lockedPlayer->id)
                ->whereNull('team_id')
                ->update(['team_id' => $team->id, 'sold_price' => $fee]);

            if ($updated === 0) {
                return ['error' => 'This player is already signed to a team.'];
            }

            $team->increment('spent', $fee);

            Transfer::create([
                'player_id' => $lockedPlayer->id,
                'from_team_id' => null,
                'to_team_id' => $team->id,
                'fee' => $fee,
                'type' => 'direct',
                'status' => 'approved',
                'requested_by_user_id' => Auth::id(),
                'approved_by_user_id' => Auth::id(),
                'effective_at' => now(),
                'notes' => "Free agent signing: {$lockedPlayer->name}",
            ]);

            return ['success' => true, 'name' => $lockedPlayer->name, 'fee' => $fee];
        });

        if (isset($result['error'])) {
            return back()->withErrors(['sign' => $result['error']]);
        }

        return back()->with('status', "{$result['name']} signed for PKR " . number_format($result['fee'], 0) . '.');
    }
}
