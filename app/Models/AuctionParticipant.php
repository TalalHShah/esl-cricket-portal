<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionParticipant extends Model
{
    /**
     * How long a participant can go quiet before being shown as "away".
     */
    public const ONLINE_THRESHOLD_SECONDS = 12;

    protected $fillable = [
        'auction_session_id',
        'team_id',
        'user_id',
        'joined_at',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function auctionSession(): BelongsTo
    {
        return $this->belongsTo(AuctionSession::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOnline(): bool
    {
        return $this->last_seen_at?->diffInSeconds(now()) <= self::ONLINE_THRESHOLD_SECONDS;
    }
}
