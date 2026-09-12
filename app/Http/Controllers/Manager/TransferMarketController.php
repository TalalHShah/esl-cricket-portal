<?php

namespace App\Http\Controllers\Manager;

use App\Concerns\Sortable;
use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Setting;
use App\Models\Team;
use App\Models\Transfer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferMarketController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $team = Auth::user()->managedTeam;
        $windowOpen = Setting::isTransferWindowOpen();

        $query = Player::with('team')
            ->transferable()
            ->whereNotNull('team_id')
            ->where('is_active', true)
            ->when($team, fn ($q) => $q->where('team_id', '!=', $team->id))
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->input('search') . '%'))
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->input('role')));

        $sort = $this->applySort($query, $request, [
            'value_desc' => fn ($q) => $q->orderByDesc('current_value'),
            'value_asc' => fn ($q) => $q->orderBy('current_value'),
            'name_asc' => fn ($q) => $q->orderBy('name'),
            'name_desc' => fn ($q) => $q->orderByDesc('name'),
            'role' => fn ($q) => $q->orderBy('role')->orderByDesc('current_value'),
            'tier' => fn ($q) => $q->orderByRaw("FIELD(tier, 'Superstar', 'Star', 'Normal', 'Low-value')"),
        ], 'value_desc');

        $listedPlayers = $query->paginate(12)->withQueryString();

        $myTransfers = collect();
        if ($team) {
            $myTransfers = Transfer::with(['player', 'fromTeam', 'toTeam'])
                ->where('to_team_id', $team->id)
                ->orWhere('from_team_id', $team->id)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
        }

        $roles = ['Batsman', 'All-rounder', 'Bowler', 'Wicketkeeper'];

        return view('manager.transfers', compact('listedPlayers', 'myTransfers', 'team', 'roles', 'windowOpen', 'sort'));
    }

    public function makeOffer(Request $request, Player $player): RedirectResponse
    {
        $team = Auth::user()->managedTeam;

        if (! $team) {
            return back()->withErrors(['offer' => 'You are not assigned to manage a team.']);
        }

        if (! Setting::isTransferWindowOpen()) {
            return back()->withErrors(['offer' => 'The transfer window is currently closed. Players are being signed through the Auction instead.']);
        }

        if ($player->is_manager_player) {
            return back()->withErrors(['offer' => 'This player cannot be transferred.']);
        }

        if (is_null($player->team_id)) {
            return back()->withErrors(['offer' => 'This player is a free agent — sign them through Scouts instead.']);
        }

        if ($player->team_id === $team->id) {
            return back()->withErrors(['offer' => 'You already own this player.']);
        }

        if (! $team->hasSquadSpace()) {
            return back()->withErrors(['offer' => 'Your squad is full (' . Team::SQUAD_LIMIT . ' players max). Release a player before making an offer.']);
        }

        $validated = $request->validate([
            'fee' => ['required', 'numeric', 'min:1'],
        ]);

        $remainingBudget = $team->remainingBudget();

        if ($remainingBudget < $validated['fee']) {
            return back()->withErrors(['offer' => 'Offer exceeds your remaining budget.']);
        }

        Transfer::create([
            'player_id' => $player->id,
            'from_team_id' => $player->team_id,
            'to_team_id' => $team->id,
            'fee' => $validated['fee'],
            'type' => 'direct',
            'status' => 'pending',
            'requested_by_user_id' => Auth::id(),
            'notes' => "Direct offer for {$player->name}",
        ]);

        return back()->with('status', "Offer of PKR " . number_format($validated['fee'], 0) . " submitted for {$player->name}.");
    }

    public function approveOffer(Transfer $transfer): RedirectResponse
    {
        $team = Auth::user()->managedTeam;

        if (! $team || $transfer->from_team_id !== $team->id) {
            return back()->withErrors(['offer' => 'You are not authorized to act on this offer.']);
        }

        if ($transfer->status !== 'pending') {
            return back()->withErrors(['offer' => 'This offer has already been resolved.']);
        }

        $result = DB::transaction(function () use ($transfer, $team) {
            $player = Player::whereKey($transfer->player_id)->lockForUpdate()->first();
            $buyerTeam = Team::whereKey($transfer->to_team_id)->lockForUpdate()->first();

            if (! $player || $player->team_id !== $team->id) {
                return ['error' => 'This player is no longer on your squad.'];
            }

            if (! $buyerTeam->hasSquadSpace()) {
                $transfer->update(['status' => 'rejected', 'notes' => $transfer->notes.' (auto-rejected: buyer squad is full)']);

                return ['error' => 'The buying team\'s squad is now full — the offer has been automatically rejected.'];
            }

            $remainingBudget = $buyerTeam->remainingBudget();
            if ($remainingBudget < (float) $transfer->fee) {
                $transfer->update(['status' => 'rejected', 'notes' => $transfer->notes.' (auto-rejected: buyer no longer has sufficient budget)']);

                return ['error' => 'The buying team no longer has sufficient budget — the offer has been automatically rejected.'];
            }

            $player->update(['team_id' => $buyerTeam->id, 'sold_price' => $transfer->fee]);
            $buyerTeam->increment('spent', $transfer->fee);
            $team->decrement('spent', min((float) $transfer->fee, (float) $team->spent));

            $transfer->update([
                'status' => 'approved',
                'approved_by_user_id' => Auth::id(),
                'effective_at' => now(),
            ]);

            return ['success' => true, 'player' => $player->name];
        });

        if (isset($result['error'])) {
            return back()->withErrors(['offer' => $result['error']]);
        }

        return back()->with('status', "Transfer approved — {$result['player']} has moved to their new team.");
    }

    public function rejectOffer(Transfer $transfer): RedirectResponse
    {
        $team = Auth::user()->managedTeam;

        if (! $team || $transfer->from_team_id !== $team->id) {
            return back()->withErrors(['offer' => 'You are not authorized to act on this offer.']);
        }

        if ($transfer->status !== 'pending') {
            return back()->withErrors(['offer' => 'This offer has already been resolved.']);
        }

        $transfer->update(['status' => 'rejected']);

        return back()->with('status', 'Offer rejected.');
    }
}
