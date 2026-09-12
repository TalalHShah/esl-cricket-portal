<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    use HasFactory;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($player) {
            if (!$player->current_value || $player->current_value === 0) {
                $player->current_value = $player->base_value;
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'country',
        'role',
        'tier',
        'base_value',
        'current_value',
        'real_life_form_modifier',
        'age',
        'batting_style',
        'bowling_style',
        'image',
        'team_id',
        'sold_price',
        'is_auctioned',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_value' => 'decimal:2',
            'current_value' => 'decimal:2',
            'real_life_form_modifier' => 'decimal:2',
            'sold_price' => 'decimal:2',
            'is_auctioned' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The team this player currently belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Per-match statistics recorded for this player.
     */
    public function matchStats(): HasMany
    {
        return $this->hasMany(MatchStat::class);
    }

    /**
     * Transfers involving this player.
     */
    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
    }

    /**
     * Auction sessions in which this player is the active lot.
     */
    public function auctionSessions(): HasMany
    {
        return $this->hasMany(AuctionSession::class);
    }

    /**
     * Determine whether the player is currently a free agent.
     */
    public function isFreeAgent(): bool
    {
        return is_null($this->team_id);
    }
}
