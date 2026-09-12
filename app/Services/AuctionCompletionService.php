<?php

namespace App\Services;

use App\Models\AuctionSession;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;

class AuctionCompletionService
{
    public function __construct(private readonly AuctionDraftService $draftService)
    {
    }

    /**
     * If this lot's bidding window has lapsed with no further action,
     * finalize it automatically — nobody should have to remember to
     * click Complete once the clock runs out.
     */
    public function completeIfExpired(AuctionSession $auction): AuctionSession
    {
        if ($auction->status === 'live' && $auction->biddingTimeExpired()) {
            $result = $this->complete($auction);

            return $result['auction'];
        }

        return $auction;
    }

    /**
     * Conclude an auction: if there's a highest bidder with room left in
     * their squad, transfer the player, deduct the buyer's budget, and
     * log the sale as an approved auction Transfer. If nobody bid, or the
     * winning team's squad filled up before the sale could be finalized,
     * the player simply stays a free agent and the session closes as
     * unsold. Shared by the admin's manual "Complete" action and the
     * manager room's auto-complete-once-everyone-else-passes flow.
     *
     * @return array{sold: bool, squad_full: bool, auction: AuctionSession}
     */
    public function complete(AuctionSession $auction, ?int $completedByUserId = null): array
    {
        $sold = false;
        $squadFull = false;

        DB::transaction(function () use ($auction, $completedByUserId, &$sold, &$squadFull) {
            if ($auction->highest_bidder_team_id && $auction->current_bid > 0) {
                $player = $auction->player;
                $team = $auction->highestBidder;

                if ($team->hasSquadSpace()) {
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
                        'requested_by_user_id' => $completedByUserId,
                        'approved_by_user_id' => $completedByUserId,
                        'effective_at' => now(),
                        'notes' => "Auction sale: {$player->name} to {$team->name}",
                    ]);

                    $sold = true;
                } else {
                    $squadFull = true;
                }
            }

            $auction->update([
                'status' => 'completed',
                'ended_at' => now(),
                'bid_deadline_at' => null,
            ]);
        });

        $auction = $auction->fresh();

        if ($auction->auction_draft_id) {
            $this->draftService->advanceAfterSale($auction);
        }

        return ['sold' => $sold, 'squad_full' => $squadFull, 'auction' => $auction];
    }
}
