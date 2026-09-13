<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\Sortable;
use App\Http\Controllers\Controller;
use App\Models\AuctionSession;
use App\Models\Player;
use App\Services\AuctionCompletionService;
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
            'tier' => fn ($q) => $q->orderByRaw("FIELD(status, 'live', 'paused', 'scheduled', 'completed', 'cancelled')")
                ->orderByRaw("FIELD(tier, '" . implode("','", AuctionSession::TIER_ORDER) . "')")
                ->orderByDesc('starting_bid'),
            'created_desc' => fn ($q) => $q->orderByDesc('created_at'),
            'status' => fn ($q) => $q->orderByRaw("FIELD(status, 'live', 'paused', 'scheduled', 'completed', 'cancelled')"),
            'bid_desc' => fn ($q) => $q->orderByDesc('current_bid'),
        ], 'tier');

        $sessions = $query->paginate(20)->withQueryString();

        return view('admin.auctions.index', compact('sessions', 'sort'));
    }

    public function create(Request $request)
    {
        $query = Player::query()->notNominated()->whereNull('team_id')->where('is_active', true);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->filled('tier')) {
            $query->where('tier', $request->input('tier'));
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('country')) {
            $query->where('country', 'like', '%' . $request->input('country') . '%');
        }

        $players = $query->orderByRaw("FIELD(tier, '" . implode("','", AuctionSession::TIER_ORDER) . "')")
            ->orderByDesc('current_value')
            ->paginate(24)
            ->withQueryString();

        $roles = ['Batsman', 'All-rounder', 'Bowler', 'Wicketkeeper'];
        $tiers = AuctionSession::TIER_ORDER;

        return view('admin.auctions.create', compact('players', 'roles', 'tiers'));
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
        $error = DB::transaction(function () use ($auction) {
            $locked = AuctionSession::whereKey($auction->id)->lockForUpdate()->first();

            if ($locked->status !== 'scheduled') {
                return 'Only scheduled auctions can be started.';
            }

            if (AuctionSession::where('status', 'live')->where('id', '!=', $locked->id)->lockForUpdate()->exists()) {
                return 'Another auction is already live. Complete or pause it first.';
            }

            $locked->update([
                'status' => 'live',
                'started_at' => now(),
                'started_by_user_id' => Auth::id(),
            ]);

            return null;
        });

        if ($error) {
            return back()->withErrors(['auction' => $error]);
        }

        return back()->with('status', "Auction for {$auction->player?->name} is now live.");
    }

    public function pause(AuctionSession $auction): RedirectResponse
    {
        if ($auction->status !== 'live') {
            return back()->withErrors(['auction' => 'Only live auctions can be paused.']);
        }

        $auction->update(['status' => 'paused', 'bid_deadline_at' => null]);

        return back()->with('status', 'Auction paused.');
    }

    public function resume(AuctionSession $auction): RedirectResponse
    {
        if ($auction->status !== 'paused') {
            return back()->withErrors(['auction' => 'Only paused auctions can be resumed.']);
        }

        $auction->update([
            'status' => 'live',
            'bid_deadline_at' => (float) $auction->current_bid > 0
                ? now()->addSeconds(AuctionSession::BID_WINDOW_SECONDS)
                : null,
        ]);

        return back()->with('status', 'Auction resumed.');
    }

    /**
     * Conclude the auction: if there's a highest bidder, transfer the
     * player to their team, deduct the team's budget, and log the sale
     * as an approved auction Transfer. If nobody bid, the player simply
     * stays a free agent and the session closes as unsold.
     */
    public function complete(AuctionSession $auction, AuctionCompletionService $completion): RedirectResponse
    {
        if (! in_array($auction->status, ['live', 'paused'], true)) {
            return back()->withErrors(['auction' => 'Only a live or paused auction can be completed.']);
        }

        $result = $completion->complete($auction, Auth::id());
        $auction = $result['auction'];

        if ($result['squad_full']) {
            $message = "{$auction->player?->name} went unsold — {$auction->highestBidder?->name} no longer had the squad space (" . \App\Models\Team::SQUAD_LIMIT . ' max) or budget to cover this bid by the time the auction was completed.';
        } elseif ($result['sold']) {
            $message = "{$auction->player?->name} sold to {$auction->highestBidder?->name} for PKR " . number_format((float) $auction->current_bid, 0) . '.';
        } else {
            $message = "{$auction->player?->name} went unsold — no bids were placed.";
        }

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
