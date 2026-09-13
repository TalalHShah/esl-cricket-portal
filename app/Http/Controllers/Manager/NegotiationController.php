<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Team;
use App\Models\Transfer;
use App\Models\TransferNegotiationRound;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * A dedicated back-and-forth negotiation flow for buying a player off
 * another team — offer, counter-offer, accept, or reject, all recorded
 * as a readable thread — instead of a single take-it-or-leave-it fee.
 * Free-agent acquisitions go through the auction (Scouts) instead; this
 * is only for players already signed to another team.
 */
class NegotiationController extends Controller
{
    public function index(): View
    {
        $team = Auth::user()->managedTeam;

        $negotiations = $team
            ? Transfer::with(['player', 'fromTeam', 'toTeam'])
                ->where('type', 'direct')
                ->where(function ($q) use ($team) {
                    $q->where('from_team_id', $team->id)->orWhere('to_team_id', $team->id);
                })
                ->orderByDesc('updated_at')
                ->paginate(15)
            : Transfer::whereRaw('1 = 0')->paginate(15);

        return view('manager.negotiations.index', compact('negotiations', 'team'));
    }

    public function show(Transfer $transfer): View
    {
        $team = Auth::user()->managedTeam;
        $this->authorizeParty($transfer, $team);

        $transfer->load(['player', 'fromTeam.manager', 'toTeam.manager', 'rounds.team']);

        return view('manager.negotiations.show', compact('transfer', 'team'));
    }

    public function counter(Request $request, Transfer $transfer): RedirectResponse
    {
        $team = Auth::user()->managedTeam;
        $this->authorizeParty($transfer, $team);

        if (! $transfer->isPending()) {
            return back()->withErrors(['negotiation' => 'This negotiation has already been resolved.']);
        }

        if (! $transfer->awaitingResponseFrom($team->id)) {
            return back()->withErrors(['negotiation' => "It's not your turn to respond — you're waiting on the other manager."]);
        }

        $validated = $request->validate([
            'fee' => ['required', 'numeric', 'min:1'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        if ($team->id === $transfer->to_team_id && $team->remainingBudget() < $validated['fee']) {
            return back()->withErrors(['negotiation' => 'That counter exceeds your remaining budget.']);
        }

        DB::transaction(function () use ($transfer, $team, $validated) {
            $locked = Transfer::whereKey($transfer->id)->lockForUpdate()->first();

            if ($locked->status !== 'pending') {
                return;
            }

            $locked->update([
                'fee' => $validated['fee'],
                'last_offer_by_team_id' => $team->id,
            ]);

            TransferNegotiationRound::create([
                'transfer_id' => $locked->id,
                'team_id' => $team->id,
                'action' => 'counter',
                'fee' => $validated['fee'],
                'message' => $validated['message'] ?? null,
                'user_id' => Auth::id(),
            ]);
        });

        return redirect()->route('manager.negotiations.show', $transfer)->with('status', 'Counter-offer sent.');
    }

    public function accept(Transfer $transfer): RedirectResponse
    {
        $team = Auth::user()->managedTeam;
        $this->authorizeParty($transfer, $team);

        if (! $transfer->isPending()) {
            return back()->withErrors(['negotiation' => 'This negotiation has already been resolved.']);
        }

        if (! $transfer->awaitingResponseFrom($team->id)) {
            return back()->withErrors(['negotiation' => "It's not your turn to respond — you're waiting on the other manager."]);
        }

        $result = DB::transaction(function () use ($transfer, $team) {
            $locked = Transfer::whereKey($transfer->id)->lockForUpdate()->first();

            if ($locked->status !== 'pending') {
                return ['error' => 'This negotiation has already been resolved.'];
            }

            $player = Player::whereKey($locked->player_id)->lockForUpdate()->first();
            $sellerTeam = Team::whereKey($locked->from_team_id)->lockForUpdate()->first();
            $buyerTeam = Team::whereKey($locked->to_team_id)->lockForUpdate()->first();

            if (! $player || $player->team_id !== $sellerTeam->id) {
                $locked->update(['status' => 'cancelled']);

                return ['error' => 'This player has already moved teams — the negotiation has been cancelled.'];
            }

            if (! $buyerTeam->hasSquadSpace()) {
                $locked->update(['status' => 'rejected']);

                return ['error' => "The buying team's squad is now full — this negotiation has been automatically cancelled."];
            }

            if ($buyerTeam->remainingBudget() < (float) $locked->fee) {
                $locked->update(['status' => 'rejected']);

                return ['error' => 'The buying team no longer has sufficient budget — this negotiation has been automatically cancelled.'];
            }

            $player->update(['team_id' => $buyerTeam->id, 'sold_price' => $locked->fee]);
            $buyerTeam->increment('spent', $locked->fee);
            $sellerTeam->decrement('spent', min((float) $locked->fee, (float) $sellerTeam->spent));

            $locked->update([
                'status' => 'approved',
                'approved_by_user_id' => Auth::id(),
                'effective_at' => now(),
            ]);

            TransferNegotiationRound::create([
                'transfer_id' => $locked->id,
                'team_id' => $team->id,
                'action' => 'accept',
                'fee' => $locked->fee,
                'user_id' => Auth::id(),
            ]);

            return ['success' => true, 'player' => $player->name];
        });

        if (isset($result['error'])) {
            return redirect()->route('manager.negotiations.show', $transfer)->withErrors(['negotiation' => $result['error']]);
        }

        return redirect()->route('manager.negotiations.show', $transfer)->with('status', "Deal done — {$result['player']} has moved teams.");
    }

    public function reject(Request $request, Transfer $transfer): RedirectResponse
    {
        $team = Auth::user()->managedTeam;
        $this->authorizeParty($transfer, $team);

        if (! $transfer->isPending()) {
            return back()->withErrors(['negotiation' => 'This negotiation has already been resolved.']);
        }

        if (! $transfer->awaitingResponseFrom($team->id)) {
            return back()->withErrors(['negotiation' => "It's not your turn to respond — you're waiting on the other manager."]);
        }

        $validated = $request->validate(['message' => ['nullable', 'string', 'max:500']]);

        DB::transaction(function () use ($transfer, $team, $validated) {
            $locked = Transfer::whereKey($transfer->id)->lockForUpdate()->first();

            if ($locked->status !== 'pending') {
                return;
            }

            $locked->update(['status' => 'rejected']);

            TransferNegotiationRound::create([
                'transfer_id' => $locked->id,
                'team_id' => $team->id,
                'action' => 'reject',
                'fee' => $locked->fee,
                'message' => $validated['message'] ?? null,
                'user_id' => Auth::id(),
            ]);
        });

        return redirect()->route('manager.negotiations.show', $transfer)->with('status', 'Offer declined.');
    }

    public function withdraw(Transfer $transfer): RedirectResponse
    {
        $team = Auth::user()->managedTeam;
        $this->authorizeParty($transfer, $team);

        if (! $transfer->isPending()) {
            return back()->withErrors(['negotiation' => 'This negotiation has already been resolved.']);
        }

        if ($transfer->last_offer_by_team_id !== $team->id) {
            return back()->withErrors(['negotiation' => 'You can only withdraw your own outstanding offer.']);
        }

        DB::transaction(function () use ($transfer, $team) {
            $locked = Transfer::whereKey($transfer->id)->lockForUpdate()->first();

            if ($locked->status !== 'pending') {
                return;
            }

            $locked->update(['status' => 'cancelled']);

            TransferNegotiationRound::create([
                'transfer_id' => $locked->id,
                'team_id' => $team->id,
                'action' => 'withdraw',
                'fee' => $locked->fee,
                'user_id' => Auth::id(),
            ]);
        });

        return redirect()->route('manager.negotiations.index')->with('status', 'Offer withdrawn.');
    }

    private function authorizeParty(Transfer $transfer, ?Team $team): void
    {
        abort_if(! $team || ! in_array($team->id, [$transfer->from_team_id, $transfer->to_team_id], true), 403);
    }
}
