<?php

namespace App\Services;

use App\Models\AuctionSession;
use App\Models\Player;
use App\Models\Team;
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
     * their squad and budget to cover the fee, transfer the player,
     * deduct the buyer's budget, and log the sale as an approved auction
     * Transfer. If nobody bid, or the winning team can no longer afford
     * or fit the player, it simply stays a free agent and the session
     * closes as unsold. Shared by the admin's manual "Complete" action,
     * the manager room's auto-complete-once-everyone-passes flow, and
     * the timer-expiry auto-complete polled from multiple browsers —
     * so this locks and rechecks the session's own status first to stay
     * safe against being called more than once for the same lot.
     *
     * @return array{sold: bool, squad_full: bool, auction: AuctionSession}
     */
    public function complete(AuctionSession $auction, ?int $completedByUserId = null): array
    {
        $sold = false;
        $squadFull = false;
        $alreadyDone = false;

        DB::transaction(function () use ($auction, $completedByUserId, &$sold, &$squadFull, &$alreadyDone) {
            $locked = AuctionSession::whereKey($auction->id)->lockForUpdate()->first();

            if (! in_array($locked->status, ['live', 'paused'], true)) {
                // Already finalized by a concurrent request (e.g. two
                // browsers polling completeIfExpired() at the same
                // deadline, or a double-click on Complete).
                $alreadyDone = true;

                return;
            }

            if ($locked->highest_bidder_team_id && $locked->current_bid > 0) {
                $player = Player::whereKey($locked->player_id)->lockForUpdate()->first();
                $team = Team::whereKey($locked->highest_bidder_team_id)->lockForUpdate()->first();

                if ($team && $team->hasSquadSpace() && $team->remainingBudget($locked->id) >= (float) $locked->current_bid) {
                    $player->update([
                        'team_id' => $team->id,
                        'sold_price' => $locked->current_bid,
                        'is_auctioned' => true,
                    ]);

                    $team->increment('spent', $locked->current_bid);

                    Transfer::create([
                        'player_id' => $player->id,
                        'from_team_id' => null,
                        'to_team_id' => $team->id,
                        'fee' => $locked->current_bid,
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

            $locked->update([
                'status' => 'completed',
                'ended_at' => now(),
                'bid_deadline_at' => null,
            ]);
        });

        $auction = $auction->fresh();

        if (! $alreadyDone && $auction->auction_draft_id) {
            $this->draftService->advanceAfterSale($auction);
        }

        return ['sold' => $sold, 'squad_full' => $squadFull, 'auction' => $auction];
    }
}
