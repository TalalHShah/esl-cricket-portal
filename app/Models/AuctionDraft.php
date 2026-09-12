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
        'burned_countries',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'turn_order' => 'array',
            'burned_countries' => 'array',
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
}
