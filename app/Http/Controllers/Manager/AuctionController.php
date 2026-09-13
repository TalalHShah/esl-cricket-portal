<?php

namespace App\Http\Controllers\Manager;

use App\Concerns\Sortable;
use App\Http\Controllers\Controller;
use App\Models\AuctionParticipant;
use App\Models\AuctionSession;
use App\Models\Team;
use App\Services\AuctionCompletionService;
use App\Support\AuctionStatePresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuctionController extends Controller
{
    use Sortable;

    public function index(Request $request): View|RedirectResponse
    {
        // A manager clicking into "Auction Room" while a lot is actually
        // live should land straight in that room — not a browsing list —
        // so the experience matches the country draft's single-room feel.
        // Skip this when explicitly asked to browse (e.g. via the lobby
        // link with ?browse=1) so completed/upcoming history stays reachable.
        if (! $request->boolean('browse')) {
            $activeSession = AuctionSession::whereIn('status', ['live', 'paused'])
                ->orderByDesc('started_at')
                ->first();

            if ($activeSession) {
                return redirect()->route('manager.auction.room', $activeSession);
            }
        }

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

        $team = Auth::user()->managedTeam;

        if ($team) {
            AuctionParticipant::updateOrCreate(
                ['auction_session_id' => $auctionSession->id, 'team_id' => $team->id],
                ['user_id' => Auth::id(), 'joined_at' => now(), 'last_seen_at' => now()]
            );
        }

        return redirect()->route('manager.auction.room', $auctionSession);
    }

    /**
     * Polled every few seconds by the auction room: refreshes this
     * team's presence heartbeat and returns the full live state
     * (bid, leader, countdown, seated managers) as JSON so the room
     * can update itself without a full page reload.
     */
    public function state(AuctionSession $auctionSession, AuctionCompletionService $completion): JsonResponse
    {
        $auctionSession = $completion->completeIfExpired($auctionSession);

        $team = Auth::user()->managedTeam;

        return response()->json(AuctionStatePresenter::present($auctionSession, $team));
    }

    public function bid(Request $request, AuctionSession $auctionSession): RedirectResponse|JsonResponse
    {
        $team = Auth::user()->managedTeam;
        $wantsJson = $request->expectsJson() || $request->ajax();

        $fail = function (string $message) use ($wantsJson) {
            return $wantsJson
                ? response()->json(['error' => $message], 422)
                : back()->withErrors(['bid' => $message]);
        };

        if (! $team) {
            return $fail('You are not assigned to manage a team.');
        }

        if (! session('auction_room_joined_' . $auctionSession->id, false)) {
            return $fail('Join the auction room before placing a bid.');
        }

        $requestedAmount = $request->filled('amount') ? (float) $request->input('amount') : null;

        $result = DB::transaction(function () use ($auctionSession, $team, $requestedAmount) {
            $locked = AuctionSession::whereKey($auctionSession->id)->lockForUpdate()->first();
            $lockedTeam = Team::whereKey($team->id)->lockForUpdate()->first();

            if ($locked->status !== 'live') {
                return ['error' => 'This auction is not currently live.'];
            }

            if ($locked->biddingTimeExpired()) {
                return ['error' => "Time's up on this lot — waiting for the auctioneer to call it."];
            }

            if (! $lockedTeam->hasSquadSpace()) {
                return ['error' => 'Your squad is full (' . Team::SQUAD_LIMIT . ' players max) — you cannot bid on another player.'];
            }

            if ($locked->highest_bidder_team_id === $lockedTeam->id) {
                return ['error' => 'You are already the highest bidder.'];
            }

            $minimumBid = $locked->current_bid > 0
                ? (float) $locked->current_bid + (float) $locked->bid_increment
                : (float) $locked->starting_bid;

            $bidAmount = $requestedAmount && $requestedAmount > $minimumBid ? $requestedAmount : $minimumBid;

            if ($lockedTeam->remainingBudget() < $bidAmount) {
                return ['error' => 'Insufficient budget to place this bid.'];
            }

            $team = $lockedTeam;

            $isFirstBid = (float) $locked->current_bid <= 0;
            $newDeadline = $isFirstBid
                ? now()->addSeconds(AuctionSession::BID_WINDOW_SECONDS)
                : ($locked->bid_deadline_at && $locked->bid_deadline_at->isFuture() ? $locked->bid_deadline_at : now())
                    ->copy()->addSeconds(AuctionSession::BID_EXTENSION_SECONDS);

            $locked->update([
                'current_bid' => $bidAmount,
                'current_team_id' => $team->id,
                'highest_bidder_team_id' => $team->id,
                'bid_deadline_at' => $newDeadline,
            ]);

            // A new highest bid reopens the floor — everyone who had
            // passed gets to reconsider against the new price.
            AuctionParticipant::where('auction_session_id', $locked->id)->update(['passed_at' => null]);

            return ['success' => true, 'amount' => $bidAmount];
        });

        if (isset($result['error'])) {
            return $fail($result['error']);
        }

        if ($wantsJson) {
            return response()->json(['success' => true, 'amount' => $result['amount']]);
        }

        return back()->with('status', 'Bid placed: PKR ' . number_format($result['amount'], 0));
    }

    /**
     * A seated team declines to bid further at the current price. Once
     * every other seated team has passed against the current highest
     * bid, the lot is sold immediately — no need to wait out the clock
     * or for the admin to step in.
     */
    public function pass(AuctionSession $auctionSession, AuctionCompletionService $completion): JsonResponse
    {
        $team = Auth::user()->managedTeam;

        if (! $team) {
            return response()->json(['error' => 'You are not assigned to manage a team.'], 422);
        }

        if (! session('auction_room_joined_' . $auctionSession->id, false)) {
            return response()->json(['error' => 'Join the auction room first.'], 422);
        }

        $result = DB::transaction(function () use ($auctionSession, $team, $completion) {
            $locked = AuctionSession::whereKey($auctionSession->id)->lockForUpdate()->first();

            if ($locked->status !== 'live') {
                return ['error' => 'This auction is not currently live.'];
            }

            if (! $locked->highest_bidder_team_id) {
                return ['error' => 'There is no bid yet to pass on.'];
            }

            if ($locked->highest_bidder_team_id === $team->id) {
                return ['error' => 'You are leading this bid — you cannot pass.'];
            }

            $participant = AuctionParticipant::where('auction_session_id', $locked->id)
                ->where('team_id', $team->id)
                ->first();

            if (! $participant) {
                return ['error' => 'Join the auction room first.'];
            }

            $participant->update(['passed_at' => now()]);

            $others = AuctionParticipant::where('auction_session_id', $locked->id)
                ->where('team_id', '!=', $locked->highest_bidder_team_id)
                ->get();

            $everyoneElsePassed = $others->isNotEmpty() && $others->every(fn (AuctionParticipant $p) => $p->hasPassed());

            if ($everyoneElsePassed) {
                $completion->complete($locked, null);

                return ['success' => true, 'completed' => true];
            }

            return ['success' => true, 'completed' => false];
        });

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }

        return response()->json($result);
    }
}
