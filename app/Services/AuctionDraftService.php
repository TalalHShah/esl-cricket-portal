<?php

namespace App\Services;

use App\Models\AuctionDraft;
use App\Models\AuctionSession;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class AuctionDraftService
{
    /**
     * Countries still worth spinning for: have at least one signable
     * (transferable, active, free-agent) player and haven't been burned
     * this cycle.
     */
    public function eligibleCountries(AuctionDraft $draft): array
    {
        $burned = $draft->burned_countries ?? [];

        $countries = Player::query()
            ->transferable()
            ->whereNull('team_id')
            ->where('is_active', true)
            ->whereNotIn('country', $burned)
            ->distinct()
            ->pluck('country')
            ->all();

        // Every remaining country has been burned this cycle, but
        // players are still out there — start a fresh cycle instead of
        // ending the draft prematurely.
        if (empty($countries) && $this->anyPlayersRemain()) {
            $draft->update(['burned_countries' => []]);

            return Player::query()
                ->transferable()
                ->whereNull('team_id')
                ->where('is_active', true)
                ->distinct()
                ->pluck('country')
                ->all();
        }

        return $countries;
    }

    public function anyPlayersRemain(): bool
    {
        return Player::query()->transferable()->whereNull('team_id')->where('is_active', true)->exists();
    }

    /**
     * Start a new draft with the given team turn order (an array of
     * team IDs). Any prior active draft is left alone — only one
     * should be started at a time, enforced by the controller.
     */
    public function start(array $teamIdsInOrder): AuctionDraft
    {
        return AuctionDraft::create([
            'status' => 'active',
            'turn_order' => array_values($teamIdsInOrder),
            'country_picker_index' => 0,
            'active_picker_index' => null,
            'current_country' => null,
            'consecutive_skips' => 0,
            'burned_countries' => [],
            'started_at' => now(),
        ]);
    }

    /**
     * The team whose turn it is spins for the next country. Picks one
     * at random from whatever's still eligible and hands the floor to
     * the next team in rotation to make the first nomination.
     */
    public function spinCountry(AuctionDraft $draft, int $requestingTeamId): array
    {
        return DB::transaction(function () use ($draft, $requestingTeamId) {
            $locked = AuctionDraft::whereKey($draft->id)->lockForUpdate()->first();

            if ($locked->status !== 'active') {
                return ['error' => 'This draft is not active.'];
            }

            if ($locked->current_country !== null) {
                return ['error' => 'A country is already in play.'];
            }

            if ($locked->countryPickerTeamId() !== $requestingTeamId) {
                return ['error' => "It's not your turn to spin for a country."];
            }

            $eligible = $this->eligibleCountries($locked);

            if (empty($eligible)) {
                $locked->update(['status' => 'completed', 'ended_at' => now()]);

                return ['completed' => true];
            }

            $country = $eligible[array_rand($eligible)];
            $count = count($locked->turn_order);

            $locked->update([
                'current_country' => $country,
                'active_picker_index' => ($locked->country_picker_index + 1) % $count,
                'consecutive_skips' => 0,
            ]);

            return ['success' => true, 'country' => $country];
        });
    }

    /**
     * The active picker brings a player from the current country into
     * the auction — their nomination counts as the opening bid at the
     * player's base value, and the floor opens to everyone else.
     */
    public function nominate(AuctionDraft $draft, int $teamId, int $playerId): array
    {
        return DB::transaction(function () use ($draft, $teamId, $playerId) {
            $locked = AuctionDraft::whereKey($draft->id)->lockForUpdate()->first();

            if ($locked->status !== 'active' || ! $locked->current_country) {
                return ['error' => 'No country is currently in play.'];
            }

            if ($locked->activePickerTeamId() !== $teamId) {
                return ['error' => "It's not your turn to pick."];
            }

            $player = Player::whereKey($playerId)->lockForUpdate()->first();

            if (! $player || $player->is_manager_player || $player->team_id !== null || ! $player->is_active) {
                return ['error' => 'That player is not available.'];
            }

            if ($player->country !== $locked->current_country) {
                return ['error' => "That player isn't from {$locked->current_country}."];
            }

            $baseValue = (float) $player->base_value;
            $increment = $baseValue < 1_000_000 ? 50_000 : 100_000;

            $session = AuctionSession::create([
                'name' => 'Draft pick: ' . $player->name,
                'status' => 'live',
                'player_id' => $player->id,
                'starting_bid' => $baseValue,
                'bid_increment' => $increment,
                'current_bid' => $baseValue,
                'current_team_id' => $teamId,
                'highest_bidder_team_id' => $teamId,
                'nominated_by_team_id' => $teamId,
                'auction_draft_id' => $locked->id,
                'started_at' => now(),
                'bid_deadline_at' => now()->addSeconds(AuctionSession::BID_WINDOW_SECONDS),
            ]);

            $locked->update(['consecutive_skips' => 0]);

            return ['success' => true, 'session' => $session];
        });
    }

    /**
     * The active picker declines to bring anyone else in from this
     * country right now. A full lap of skips with nobody nominating
     * burns the country.
     */
    public function skipTurn(AuctionDraft $draft, int $teamId): array
    {
        return DB::transaction(function () use ($draft, $teamId) {
            $locked = AuctionDraft::whereKey($draft->id)->lockForUpdate()->first();

            if ($locked->status !== 'active' || ! $locked->current_country) {
                return ['error' => 'No country is currently in play.'];
            }

            if ($locked->activePickerTeamId() !== $teamId) {
                return ['error' => "It's not your turn to pick."];
            }

            $count = count($locked->turn_order);
            $skips = $locked->consecutive_skips + 1;

            if ($skips >= $count) {
                $this->burnCurrentCountry($locked);

                return ['success' => true, 'burned' => true];
            }

            $locked->active_picker_index = ($locked->active_picker_index + 1) % $count;
            $locked->consecutive_skips = $skips;
            $locked->save();

            return ['success' => true, 'burned' => false];
        });
    }

    /**
     * Called once a nominated player's mini-auction resolves (sold or
     * unsold): hand the floor to the next picker for the same country,
     * or burn the country if no signable players are left in it.
     */
    public function advanceAfterSale(AuctionSession $session): void
    {
        if (! $session->auction_draft_id) {
            return;
        }

        DB::transaction(function () use ($session) {
            $draft = AuctionDraft::whereKey($session->auction_draft_id)->lockForUpdate()->first();

            if (! $draft || $draft->status !== 'active' || $draft->current_country === null) {
                return;
            }

            $remaining = Player::query()
                ->transferable()
                ->whereNull('team_id')
                ->where('is_active', true)
                ->where('country', $draft->current_country)
                ->exists();

            if (! $remaining) {
                $this->burnCurrentCountry($draft);

                return;
            }

            $count = count($draft->turn_order);
            $draft->active_picker_index = ($draft->active_picker_index + 1) % $count;
            $draft->consecutive_skips = 0;
            $draft->save();
        });
    }

    private function burnCurrentCountry(AuctionDraft $draft): void
    {
        $burned = $draft->burned_countries ?? [];
        $burned[] = $draft->current_country;
        $count = count($draft->turn_order);

        $draft->update([
            'burned_countries' => array_values(array_unique($burned)),
            'current_country' => null,
            'active_picker_index' => null,
            'consecutive_skips' => 0,
            'country_picker_index' => ($draft->country_picker_index + 1) % $count,
        ]);

        if (! $this->anyPlayersRemain()) {
            $draft->update(['status' => 'completed', 'ended_at' => now()]);
        }
    }
}
