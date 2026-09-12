<?php

namespace App\Http\Controllers\Manager;

use App\Concerns\Sortable;
use App\Http\Controllers\Controller;
use App\Models\AuctionSession;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuctionController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $liveQuery = AuctionSession::with(['player', 'currentTeam', 'highestBidder'])
            ->whereIn('status', ['live', 'paused']);

        $liveSort = $this->applySort($liveQuery, $request, [
            'started_desc' => fn ($q) => $q->orderByDesc('started_at'),
            'bid_desc' => fn ($q) => $q->orderByDesc('current_bid'),
            'bid_asc' => fn ($q) => $q->orderBy('current_bid'),
        ], 'started_desc', 'live_sort');

        $liveSessions = $liveQuery->get();

        $upcomingQuery = AuctionSession::with('player')->where('status', 'scheduled');

        $upcomingSort = $this->applySort($upcomingQuery, $request, [
            'created_asc' => fn ($q) => $q->orderBy('created_at'),
            'starting_bid_desc' => fn ($q) => $q->orderByDesc('starting_bid'),
            'starting_bid_asc' => fn ($q) => $q->orderBy('starting_bid'),
        ], 'created_asc', 'upcoming_sort');

        $upcomingSessions = $upcomingQuery->limit(10)->get();

        $completedQuery = AuctionSession::with(['player', 'highestBidder'])->where('status', 'completed');

        $completedSort = $this->applySort($completedQuery, $request, [
            'ended_desc' => fn ($q) => $q->orderByDesc('ended_at'),
            'fee_desc' => fn ($q) => $q->orderByDesc('current_bid'),
            'fee_asc' => fn ($q) => $q->orderBy('current_bid'),
        ], 'ended_desc', 'completed_sort');

        $completedSessions = $completedQuery->limit(10)->get();

        $team = Auth::user()->managedTeam;

        return view('manager.auction', compact(
            'liveSessions', 'upcomingSessions', 'completedSessions', 'team',
            'liveSort', 'upcomingSort', 'completedSort'
        ));
    }

    public function room(AuctionSession $auctionSession): View
    {
        $auctionSession->load(['player', 'currentTeam', 'highestBidder']);
        $team = Auth::user()->managedTeam;
        $joined = session('auction_room_joined_' . $auctionSession->id, false);

        return view('manager.auction-room', compact('auctionSession', 'team', 'joined'));
    }

    public function join(AuctionSession $auctionSession): RedirectResponse
    {
        session(['auction_room_joined_' . $auctionSession->id => true]);

        return redirect()->route('manager.auction.room', $auctionSession);
    }

    public function bid(Request $request, AuctionSession $auctionSession): RedirectResponse
    {
        $team = Auth::user()->managedTeam;

        if (! $team) {
            return back()->withErrors(['bid' => 'You are not assigned to manage a team.']);
        }

        if (! session('auction_room_joined_' . $auctionSession->id, false)) {
            return back()->withErrors(['bid' => 'Join the auction room before placing a bid.']);
        }

        $result = DB::transaction(function () use ($auctionSession, $team) {
            $locked = AuctionSession::whereKey($auctionSession->id)->lockForUpdate()->first();

            if ($locked->status !== 'live') {
                return ['error' => 'This auction is not currently live.'];
            }

            if ($locked->highest_bidder_team_id === $team->id) {
                return ['error' => 'You are already the highest bidder.'];
            }

            $minimumBid = $locked->current_bid > 0
                ? $locked->current_bid + $locked->bid_increment
                : $locked->starting_bid;

            if ($team->remainingBudget() < $minimumBid) {
                return ['error' => 'Insufficient budget to place this bid.'];
            }

            $locked->update([
                'current_bid' => $minimumBid,
                'current_team_id' => $team->id,
                'highest_bidder_team_id' => $team->id,
            ]);

            return ['success' => true, 'amount' => $minimumBid];
        });

        if (isset($result['error'])) {
            return back()->withErrors(['bid' => $result['error']]);
        }

        return back()->with('status', 'Bid placed: PKR ' . number_format($result['amount'], 0));
    }
}
