<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuctionDraft extends Model
{
    protected $fillable = [
        'status',
        'turn_order',
        'country_picker_index',
        'active_picker_index',
        'current_country',
        'consecutive_skips',
        'passed_team_ids',
        'turn_deadline_at',
        'burned_countries',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'turn_order' => 'array',
            'burned_countries' => 'array',
            'passed_team_ids' => 'array',
            'turn_deadline_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(AuctionSession::class);
    }

    /**
     * The team_id whose turn it is to spin for the next country.
     */
    public function countryPickerTeamId(): ?int
    {
        return $this->turn_order[$this->country_picker_index] ?? null;
    }

    /**
     * The team_id whose turn it is to nominate a player (or skip) from
     * the current country.
     */
    public function activePickerTeamId(): ?int
    {
        if ($this->active_picker_index === null) {
            return null;
        }

        return $this->turn_order[$this->active_picker_index] ?? null;
    }

    public function advanceActivePicker(): void
    {
        $count = count($this->turn_order);
        $this->active_picker_index = ($this->active_picker_index + 1) % $count;
    }

    /**
     * Teams who have passed (explicitly, or by letting their turn
     * timer expire) on the country currently in play — they're skipped
     * over in the picking rotation until either the country changes or
     * they choose to jump back in.
     */
    public function passedTeamIds(): array
    {
        return $this->passed_team_ids ?? [];
    }

    /**
     * Teams from the turn order still eligible to be asked for a pick
     * on the current country.
     */
    public function remainingTeamIds(): array
    {
        return array_values(array_diff($this->turn_order ?? [], $this->passedTeamIds()));
    }

    public function hasTeamPassed(?int $teamId): bool
    {
        return $teamId !== null && in_array($teamId, $this->passedTeamIds(), true);
    }

    /**
     * Whether the turn timer (spin or pick) has lapsed with no action
     * taken.
     */
    public function turnExpired(): bool
    {
        return $this->turn_deadline_at !== null && now()->greaterThan($this->turn_deadline_at);
    }

    /**
     * Walk forward from the given turn_order index to the next index
     * whose team hasn't passed on the current country, wrapping around.
     * Returns null if every team has passed.
     */
    public function nextEligibleIndex(int $fromIndex): ?int
    {
        $count = count($this->turn_order);
        $remaining = $this->remainingTeamIds();

        if (empty($remaining)) {
            return null;
        }

        $index = $fromIndex;

        for ($i = 0; $i < $count; $i++) {
            $index = ($index + 1) % $count;

            if (in_array($this->turn_order[$index], $remaining, true)) {
                return $index;
            }
        }

        return null;
    }
}
