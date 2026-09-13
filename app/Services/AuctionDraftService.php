<?php

namespace App\Services;

use App\Models\AuctionDraft;
use App\Models\AuctionSession;
use App\Models\Player;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class AuctionDraftService
{
    /**
     * Fallback if the admin hasn't configured one yet. Prefer
     * turnTimeoutSeconds() below, which reads the live admin setting.
     */
    public const DEFAULT_TURN_TIMEOUT_SECONDS = 90;

    /**
     * How long a manager gets to act on their turn — spinning for a
     * country, or nominating a player / passing — before it's treated
     * as an automatic pass. Admin-configurable (Settings > Draft Timer)
     * so the league can tune it without a code change; keeps the room
     * from stalling indefinitely on one manager who stepped away.
     */
    public function turnTimeoutSeconds(): int
    {
        return max(10, (int) Setting::getValue('draft.turn_timeout_seconds', self::DEFAULT_TURN_TIMEOUT_SECONDS));
    }

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
            ->notNominated()
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
                ->notNominated()
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
        return Player::query()->transferable()->notNominated()->whereNull('team_id')->where('is_active', true)->exists();
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
            'passed_team_ids' => [],
            'turn_deadline_at' => now()->addSeconds($this->turnTimeoutSeconds()),
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
                'passed_team_ids' => [],
                'turn_deadline_at' => now()->addSeconds($this->turnTimeoutSeconds()),
            ]);

            return ['success' => true, 'country' => $country];
        });
    }

    /**
     * The active picker reserves a player from the current country for
     * the auction — this is a selection, not a purchase. No money
     * changes hands and nobody bids here; the player is simply queued
     * (as a 'scheduled' AuctionSession, tagged with their tier) for the
     * separate Auction phase, which the admin runs later — possibly on
     * a different day — working through the queue one tier at a time
     * (Platinum, then Diamond, then Gold, then Silver). The picking
     * rotation moves on immediately afterward, same as a pass.
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

            if (AuctionSession::where('player_id', $player->id)->whereIn('status', ['scheduled', 'live', 'paused'])->exists()) {
                return ['error' => 'That player has already been picked for the auction pool.'];
            }

            $baseValue = (float) $player->base_value;
            $increment = $baseValue < 1_000_000 ? 50_000 : 100_000;

            $session = AuctionSession::create([
                'name' => 'Draft pick: ' . $player->name,
                'status' => 'scheduled',
                'player_id' => $player->id,
                'tier' => $player->tier,
                'starting_bid' => $baseValue,
                'bid_increment' => $increment,
                'current_bid' => 0,
                'nominated_by_team_id' => $teamId,
                'auction_draft_id' => $locked->id,
                'source' => 'draft',
            ]);

            $this->advanceAfterPick($locked);

            return ['success' => true, 'session' => $session];
        });
    }

    /**
     * Move the picking rotation on to the next eligible manager after a
     * pick, or retire the country if it's out of signable players or
     * out of managers willing to pick from it. Assumes the caller
     * already holds the lock on $draft.
     */
    private function advanceAfterPick(AuctionDraft $draft): void
    {
        $remaining = Player::query()
            ->transferable()
            ->notNominated()
            ->whereNull('team_id')
            ->where('is_active', true)
            ->where('country', $draft->current_country)
            ->exists();

        if (! $remaining) {
            $this->burnCurrentCountry($draft);

            return;
        }

        $nextIndex = $draft->nextEligibleIndex($draft->active_picker_index);

        if ($nextIndex === null) {
            $this->burnCurrentCountry($draft);

            return;
        }

        $draft->update([
            'active_picker_index' => $nextIndex,
            'consecutive_skips' => 0,
            'turn_deadline_at' => now()->addSeconds($this->turnTimeoutSeconds()),
        ]);
    }

    /**
     * The active picker declines to bring anyone else in from this
     * country — whether by clicking Pass themselves, or by letting
     * their turn timer run out (see autoAdvanceIfExpired). They're
     * permanently out of the picking rotation for this country: the
     * turn moves to the next manager who hasn't passed, and this one
     * won't be asked again until either the country changes or they
     * explicitly rejoin() while it's still in play.
     */
    public function skipTurn(AuctionDraft $draft, int $teamId, bool $auto = false): array
    {
        return DB::transaction(function () use ($draft, $teamId, $auto) {
            $locked = AuctionDraft::whereKey($draft->id)->lockForUpdate()->first();

            if ($locked->status !== 'active' || ! $locked->current_country) {
                return ['error' => 'No country is currently in play.'];
            }

            if ($locked->activePickerTeamId() !== $teamId) {
                return ['error' => "It's not your turn to pick."];
            }

            $passed = $locked->passedTeamIds();
            if (! in_array($teamId, $passed, true)) {
                $passed[] = $teamId;
            }
            $locked->passed_team_ids = $passed;

            $nextIndex = $locked->nextEligibleIndex($locked->active_picker_index);

            if ($nextIndex === null) {
                $locked->save();
                $this->burnCurrentCountry($locked->fresh());

                return ['success' => true, 'burned' => true, 'auto' => $auto];
            }

            $locked->update([
                'passed_team_ids' => $passed,
                'active_picker_index' => $nextIndex,
                'turn_deadline_at' => now()->addSeconds($this->turnTimeoutSeconds()),
            ]);

            return ['success' => true, 'burned' => false, 'auto' => $auto];
        });
    }

    /**
     * A manager who passed on this country changes their mind while
     * it's still in play — they rejoin the picking rotation. Doesn't
     * jump the queue; they'll simply be asked again once the rotation
     * comes back around to their original seat.
     */
    public function rejoin(AuctionDraft $draft, int $teamId): array
    {
        return DB::transaction(function () use ($draft, $teamId) {
            $locked = AuctionDraft::whereKey($draft->id)->lockForUpdate()->first();

            if ($locked->status !== 'active' || ! $locked->current_country) {
                return ['error' => 'No country is currently in play.'];
            }

            if (! $locked->hasTeamPassed($teamId)) {
                return ['error' => "You haven't passed on this country."];
            }

            $locked->update([
                'passed_team_ids' => array_values(array_diff($locked->passedTeamIds(), [$teamId])),
            ]);

            return ['success' => true];
        });
    }

    /**
     * The current turn — spinning for a country, or nominating/passing
     * from one — has run past its deadline with nobody acting. Auto-
     * spin on the country picker's behalf, or auto-pass the active
     * picker, so the room never stalls indefinitely on one manager.
     * Safe to call on every poll; it's a no-op unless truly expired.
     */
    public function autoAdvanceIfExpired(AuctionDraft $draft): AuctionDraft
    {
        if ($draft->status !== 'active' || ! $draft->turnExpired()) {
            return $draft;
        }

        if (AuctionSession::where('auction_draft_id', $draft->id)->whereIn('status', ['live', 'paused'])->exists()) {
            return $draft;
        }

        if ($draft->current_country === null) {
            $pickerId = $draft->countryPickerTeamId();
            if ($pickerId !== null) {
                $this->spinCountry($draft, $pickerId);
            }
        } else {
            $pickerId = $draft->activePickerTeamId();
            if ($pickerId !== null) {
                $this->skipTurn($draft, $pickerId, auto: true);
            }
        }

        return $draft->fresh();
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
            'passed_team_ids' => [],
            'consecutive_skips' => 0,
            'country_picker_index' => ($draft->country_picker_index + 1) % $count,
            'turn_deadline_at' => now()->addSeconds($this->turnTimeoutSeconds()),
        ]);

        if (! $this->anyPlayersRemain()) {
            $draft->update(['status' => 'completed', 'ended_at' => now(), 'turn_deadline_at' => null]);
        }
    }
}
