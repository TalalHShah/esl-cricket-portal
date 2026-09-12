<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionSession extends Model
{
    use HasFactory;

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
}
