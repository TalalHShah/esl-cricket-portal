<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\Sortable;
use App\Http\Controllers\Controller;
use App\Models\AuctionSession;
use App\Models\Player;
use App\Models\Transfer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuctionSessionController extends Controller
{
    use Sortable;

    public function index(Request $request)
    {
        $query = AuctionSession::with(['player', 'currentTeam', 'highestBidder']);

        $sort = $this->applySort($query, $request, [
            'created_desc' => fn ($q) => $q->orderByDesc('created_at'),
            'status' => fn ($q) => $q->orderByRaw("FIELD(status, 'live', 'paused', 'scheduled', 'completed', 'cancelled')"),
            'bid_desc' => fn ($q) => $q->orderByDesc('current_bid'),
        ], 'created_desc');

        $sessions = $query->paginate(20)->withQueryString();

        return view('admin.auctions.index', compact('sessions', 'sort'));
    }

    public function create()
    {
        $players = Player::whereNull('team_id')->where('is_active', true)->orderBy('name')->get();

        return view('admin.auctions.create', compact('players'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'player_id' => ['required', 'exists:players,id'],
            'starting_bid' => ['required', 'numeric', 'min:1'],
            'bid_increment' => ['required', 'numeric', 'min:1'],
        ]);

        $player = Player::findOrFail($validated['player_id']);

        if ($player->team_id !== null) {
            return back()->withErrors(['player_id' => 'This player is already signed to a team.'])->withInput();
        }

        if (AuctionSession::where('player_id', $player->id)->whereIn('status', ['scheduled', 'live', 'paused'])->exists()) {
            return back()->withErrors(['player_id' => 'This player already has an active or scheduled auction session.'])->withInput();
        }

        AuctionSession::create([
            'name' => 'Auction: ' . $player->name,
            'status' => 'scheduled',
            'player_id' => $player->id,
            'starting_bid' => $validated['starting_bid'],
            'bid_increment' => $validated['bid_increment'],
            'current_bid' => 0,
        ]);

        return redirect()->route('admin.auctions.index')->with('status', "Auction created for {$player->name}.");
    }

    public function start(AuctionSession $auction): RedirectResponse
    {
        if ($auction->status !== 'scheduled') {
            return back()->withErrors(['auction' => 'Only scheduled auctions can be started.']);
        }

        if (AuctionSession::where('status', 'live')->where('id', '!=', $auction->id)->exists()) {
            return back()->withErrors(['auction' => 'Another auction is already live. Complete or pause it first.']);
        }

        $auction->update([
            'status' => 'live',
            'started_at' => now(),
            'started_by_user_id' => Auth::id(),
        ]);

        return back()->with('status', "Auction for {$auction->player?->name} is now live.");
    }

    public function pause(AuctionSession $auction): RedirectResponse
    {
        if ($auction->status !== 'live') {
            return back()->withErrors(['auction' => 'Only live auctions can be paused.']);
        }

        $auction->update(['status' => 'paused']);

        return back()->with('status', 'Auction paused.');
    }

    public function resume(AuctionSession $auction): RedirectResponse
    {
        if ($auction->status !== 'paused') {
            return back()->withErrors(['auction' => 'Only paused auctions can be resumed.']);
        }

        $auction->update(['status' => 'live']);

        return back()->with('status', 'Auction resumed.');
    }

    /**
     * Conclude the auction: if there's a highest bidder, transfer the
     * player to their team, deduct the team's budget, and log the sale
     * as an approved auction Transfer. If nobody bid, the player simply
     * stays a free agent and the session closes as unsold.
     */
    public function complete(AuctionSession $auction): RedirectResponse
    {
        if (! in_array($auction->status, ['live', 'paused'], true)) {
            return back()->withErrors(['auction' => 'Only a live or paused auction can be completed.']);
        }

        DB::transaction(function () use ($auction) {
            if ($auction->highest_bidder_team_id && $auction->current_bid > 0) {
                $player = $auction->player;
                $team = $auction->highestBidder;

                $player->update([
                    'team_id' => $team->id,
                    'sold_price' => $auction->current_bid,
                    'is_auctioned' => true,
                ]);

                $team->increment('spent', $auction->current_bid);

                Transfer::create([
                    'player_id' => $player->id,
                    'from_team_id' => null,
                    'to_team_id' => $team->id,
                    'fee' => $auction->current_bid,
                    'type' => 'auction',
                    'status' => 'approved',
                    'requested_by_user_id' => Auth::id(),
                    'approved_by_user_id' => Auth::id(),
                    'effective_at' => now(),
                    'notes' => "Auction sale: {$player->name} to {$team->name}",
                ]);
            }

            $auction->update([
                'status' => 'completed',
                'ended_at' => now(),
            ]);
        });

        $auction->refresh();

        $message = $auction->highest_bidder_team_id
            ? "{$auction->player?->name} sold to {$auction->highestBidder?->name} for PKR " . number_format((float) $auction->current_bid, 0) . '.'
            : "{$auction->player?->name} went unsold — no bids were placed.";

        return back()->with('status', $message);
    }

    public function cancel(AuctionSession $auction): RedirectResponse
    {
        if (in_array($auction->status, ['completed', 'cancelled'], true)) {
            return back()->withErrors(['auction' => 'This auction has already concluded.']);
        }

        $auction->update(['status' => 'cancelled', 'ended_at' => now()]);

        return back()->with('status', 'Auction cancelled.');
    }

    public function destroy(AuctionSession $auction): RedirectResponse
    {
        if ($auction->status !== 'scheduled') {
            return back()->withErrors(['auction' => 'Only scheduled (not-yet-started) auctions can be deleted. Cancel a live one instead.']);
        }

        $auction->delete();

        return redirect()->route('admin.auctions.index')->with('status', 'Auction session deleted.');
    }
}
