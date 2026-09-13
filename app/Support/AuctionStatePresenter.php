<?php

namespace App\Support;

use App\Models\AuctionParticipant;
use App\Models\AuctionSession;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;

/**
 * Builds the JSON payload the auction bidding UI polls, and records the
 * current viewer's presence heartbeat while doing so. Shared by the
 * standalone auction room and the merged draft room, so both surfaces
 * stay in sync from one implementation.
 */
class AuctionStatePresenter
{
    public static function present(AuctionSession $auctionSession, ?Team $team): array
    {
        if ($team && session('auction_room_joined_' . $auctionSession->id, false)) {
            $participant = AuctionParticipant::firstOrNew(
                ['auction_session_id' => $auctionSession->id, 'team_id' => $team->id]
            );
            if (! $participant->exists) {
                $participant->joined_at = now();
            }
            $participant->user_id = Auth::id();
            $participant->last_seen_at = now();
            $participant->save();
        }

        $auctionSession->load(['player', 'highestBidder.manager']);

        $saleResult = null;
        if (in_array($auctionSession->status, ['completed', 'cancelled'], true)) {
            $saleResult = $auctionSession->status === 'cancelled'
                ? 'cancelled'
                : ($auctionSession->highest_bidder_team_id && $auctionSession->player?->team_id === $auctionSession->highest_bidder_team_id
                    ? 'sold'
                    : 'unsold');
        }

        $participants = AuctionParticipant::with(['team.manager'])
            ->where('auction_session_id', $auctionSession->id)
            ->get()
            ->map(fn (AuctionParticipant $p) => [
                'team_id' => $p->team_id,
                'team_name' => $p->team?->name,
                'team_short_name' => $p->team?->short_name,
                'team_logo' => $p->team?->logo ? asset('storage/' . $p->team->logo) : null,
                'manager_name' => $p->team?->manager?->name,
                'manager_avatar' => $p->team?->manager?->avatar ? asset('storage/' . $p->team->manager->avatar) : null,
                'is_leading' => $p->team_id === $auctionSession->highest_bidder_team_id,
                'is_online' => $p->isOnline(),
                'is_you' => $team && $p->team_id === $team->id,
                'has_passed' => $p->hasPassed(),
            ])
            ->values();

        $minimumBid = $auctionSession->current_bid > 0
            ? (float) $auctionSession->current_bid + (float) $auctionSession->bid_increment
            : (float) $auctionSession->starting_bid;

        $myParticipant = $team
            ? AuctionParticipant::where('auction_session_id', $auctionSession->id)->where('team_id', $team->id)->first()
            : null;

        $shortlist = [];
        $isPlayerShortlisted = false;
        if ($team) {
            $shortlisted = $team->shortlistedPlayers()
                ->whereNull('players.team_id')
                ->where('players.is_active', true)
                ->orderBy('players.name')
                ->get(['players.id', 'players.name', 'players.country', 'players.role', 'players.tier', 'players.base_value']);

            $isPlayerShortlisted = $shortlisted->contains('id', $auctionSession->player_id);

            $shortlist = $shortlisted->map(fn (Player $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'country' => $p->country,
                'role' => $p->role,
                'tier' => $p->tier,
                'base_value' => (float) $p->base_value,
                'is_current_lot' => $p->id === $auctionSession->player_id,
            ])->values();
        }

        return [
            'session_id' => $auctionSession->id,
            'status' => $auctionSession->status,
            'sale_result' => $saleResult,
            'player_name' => $auctionSession->player?->name,
            'is_shortlisted' => $isPlayerShortlisted,
            'shortlist' => $shortlist,
            'current_bid' => (float) $auctionSession->current_bid,
            'starting_bid' => (float) $auctionSession->starting_bid,
            'bid_increment' => (float) $auctionSession->bid_increment,
            'minimum_bid' => $minimumBid,
            'leader' => $auctionSession->highestBidder ? [
                'team_id' => $auctionSession->highestBidder->id,
                'team_name' => $auctionSession->highestBidder->name,
                'team_short_name' => $auctionSession->highestBidder->short_name,
                'team_logo' => $auctionSession->highestBidder->logo ? asset('storage/' . $auctionSession->highestBidder->logo) : null,
                'manager_name' => $auctionSession->highestBidder->manager?->name,
                'manager_avatar' => $auctionSession->highestBidder->manager?->avatar ? asset('storage/' . $auctionSession->highestBidder->manager->avatar) : null,
                'primary_color' => $auctionSession->highestBidder->primary_color,
                'secondary_color' => $auctionSession->highestBidder->secondary_color,
            ] : null,
            'deadline_at' => $auctionSession->bid_deadline_at?->toIso8601String(),
            'time_expired' => $auctionSession->biddingTimeExpired(),
            'participants' => $participants,
            'you' => $team ? [
                'team_id' => $team->id,
                'joined' => (bool) session('auction_room_joined_' . $auctionSession->id, false),
                'remaining_budget' => $team->remainingBudget(),
                'has_squad_space' => $team->hasSquadSpace(),
                'is_leading' => $team->id === $auctionSession->highest_bidder_team_id,
                'has_passed' => $myParticipant?->hasPassed() ?? false,
            ] : null,
        ];
    }
}
