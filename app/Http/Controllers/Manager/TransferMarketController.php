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

class TransferMarketController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $team = Auth::user()->managedTeam;
        $windowOpen = Setting::getValue('transfer_window.open', true);

        $query = Player::with('team')
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

        if (! Setting::getValue('transfer_window.open', true)) {
            return back()->withErrors(['offer' => 'The transfer window is currently closed. Players are being signed through the Auction instead.']);
        }

        $validated = $request->validate([
            'fee' => ['required', 'numeric', 'min:1'],
        ]);

        $remainingBudget = $team->budget - $team->spent;

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
}
