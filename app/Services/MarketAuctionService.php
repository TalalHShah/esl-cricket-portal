<?php

namespace App\Services;

use App\Models\AuctionSession;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Turns "sign this free agent" into a real, biddable auction instead of
 * an instant transfer: the first manager to act opens the lot at the
 * player's current value, and the whole league gets a full hour to
 * outbid them before it's called. Reuses the same AuctionSession model
 * and bidding room as the admin/draft auctions — a market lot is just
 * one that can run independently and concurrently with others, rather
 * than one-at-a-time.
 */
class MarketAuctionService
{
    /**
     * Open a free agent for bidding, or hand back the auction already
     * in progress for them if one exists — a manager can never create a
     * duplicate lot for the same player.
     *
     * @return array{error?: string, session?: AuctionSession, already_open?: bool}
     */
    public function open(Player $player, int $teamId): array
    {
        return DB::transaction(function () use ($player, $teamId) {
            $lockedPlayer = Player::whereKey($player->id)->lockForUpdate()->first();

            if (! $lockedPlayer || $lockedPlayer->is_manager_player || ! $lockedPlayer->is_active) {
                return ['error' => 'This player is not available for auction.'];
            }

            if ($lockedPlayer->team_id !== null) {
                return ['error' => 'This player is already signed to a team — submit a transfer offer instead.'];
            }

            $existing = AuctionSession::where('player_id', $lockedPlayer->id)
                ->whereIn('status', ['scheduled', 'live', 'paused'])
                ->first();

            if ($existing) {
                return ['success' => true, 'session' => $existing, 'already_open' => true];
            }

            $team = Team::whereKey($teamId)->lockForUpdate()->first();

            if (! $team || ! $team->hasSquadSpace()) {
                return ['error' => 'Your squad is full (' . Team::SQUAD_LIMIT . ' players max) — you cannot open an auction for another player.'];
            }

            $startingBid = (float) $lockedPlayer->current_value;

            if ($team->remainingBudget() < $startingBid) {
                return ['error' => 'You do not have enough budget to open bidding at this player\'s value.'];
            }

            $increment = $startingBid < 1_000_000 ? 50_000 : 100_000;

            $session = AuctionSession::create([
                'name' => 'Free agent auction: ' . $lockedPlayer->name,
                'status' => 'live',
                'player_id' => $lockedPlayer->id,
                'starting_bid' => $startingBid,
                'bid_increment' => $increment,
                'current_bid' => $startingBid,
                'current_team_id' => $team->id,
                'highest_bidder_team_id' => $team->id,
                'nominated_by_team_id' => $team->id,
                'source' => 'market',
                'started_by_user_id' => Auth::id(),
                'started_at' => now(),
                'bid_deadline_at' => now()->addSeconds(AuctionSession::MARKET_WINDOW_SECONDS),
            ]);

            return ['success' => true, 'session' => $session, 'already_open' => false];
        });
    }
}
