<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\AuctionSession;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuctionController extends Controller
{
    public function index(): View
    {
        $liveSessions = AuctionSession::with(['player', 'currentTeam', 'highestBidder'])
            ->whereIn('status', ['live', 'paused'])
            ->orderByDesc('started_at')
            ->get();

        $upcomingSessions = AuctionSession::with('player')
            ->where('status', 'scheduled')
            ->orderBy('created_at')
            ->limit(10)
            ->get();

        $completedSessions = AuctionSession::with(['player', 'highestBidder'])
            ->where('status', 'completed')
            ->latest('ended_at')
            ->limit(10)
            ->get();

        $team = Auth::user()->managedTeam;

        return view('manager.auction', compact('liveSessions', 'upcomingSessions', 'completedSessions', 'team'));
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

        if ($auctionSession->status !== 'live') {
            return back()->withErrors(['bid' => 'This auction is not currently live.']);
        }

        $minimumBid = $auctionSession->current_bid > 0
            ? $auctionSession->current_bid + $auctionSession->bid_increment
            : $auctionSession->starting_bid;

        $remainingBudget = $team->budget - $team->spent;

        if ($remainingBudget < $minimumBid) {
            return back()->withErrors(['bid' => 'Insufficient budget to place this bid.']);
        }

        DB::transaction(function () use ($auctionSession, $team, $minimumBid) {
            $auctionSession->update([
                'current_bid' => $minimumBid,
                'current_team_id' => $team->id,
                'highest_bidder_team_id' => $team->id,
            ]);
        });

        return back()->with('status', 'Bid placed: PKR ' . number_format($minimumBid, 0));
    }
}
