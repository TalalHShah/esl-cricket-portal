<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuctionSession extends Model
{
    use HasFactory;

    /**
     * How long, in seconds, bidding stays open after each bid before the
     * lot is considered ready to be called (mirrors a real auction's
     * "going once, going twice" countdown). Each new bid resets the clock.
     */
    public const BID_WINDOW_SECONDS = 30;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'status',
        'player_id',
        'current_team_id',
        'current_bid',
        'starting_bid',
        'bid_increment',
        'highest_bidder_team_id',
        'started_by_user_id',
        'started_at',
        'ended_at',
        'bid_deadline_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'current_bid' => 'decimal:2',
            'starting_bid' => 'decimal:2',
            'bid_increment' => 'decimal:2',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'bid_deadline_at' => 'datetime',
        ];
    }

    /**
     * The player currently being auctioned.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * The team currently holding the bidding rights.
     */
    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    /**
     * The team holding the highest bid.
     */
    public function highestBidder(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'highest_bidder_team_id');
    }

    /**
     * The user who started the auction session.
     */
    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by_user_id');
    }

    /**
     * Determine whether the auction session is currently live.
     */
    public function isLive(): bool
    {
        return $this->status === 'live';
    }

    /**
     * Teams that have taken a seat in this auction's room.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(AuctionParticipant::class);
    }

    /**
     * Whether the bidding window has expired without a new bid — i.e.
     * the lot is ready to be called for the highest bidder.
     */
    public function biddingTimeExpired(): bool
    {
        if (! $this->isLive() || ! $this->bid_deadline_at) {
            return false;
        }

        return now()->greaterThan($this->bid_deadline_at);
    }
}
