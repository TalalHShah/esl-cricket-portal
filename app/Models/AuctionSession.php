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
     * How long, in seconds, the clock runs from the very first bid on a
     * lot. Subsequent bids don't reset this back to the full window —
     * they only add BID_EXTENSION_SECONDS, so an active bidding war
     * creeps the deadline forward instead of restarting a full countdown
     * on every single bid.
     */
    public const BID_WINDOW_SECONDS = 180;

    /**
     * How long, in seconds, each bid after the first adds to the clock
     * (an anti-snipe extension, same idea as eBay/auction-house
     * "going once, going twice" rules).
     */
    public const BID_EXTENSION_SECONDS = 30;

    /**
     * How long a manager-initiated free-agent market auction runs from
     * the moment it's opened — a full hour, per league rules, so anyone
     * in the league has a real chance to see it and outbid.
     */
    public const MARKET_WINDOW_SECONDS = 3600;

    /**
     * Auction category order — the main auction phase works through
     * the nominated pool one tier at a time, most premium first.
     */
    public const TIER_ORDER = ['Platinum', 'Diamond', 'Gold', 'Silver'];

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
        'tier',
        'current_team_id',
        'current_bid',
        'starting_bid',
        'bid_increment',
        'highest_bidder_team_id',
        'nominated_by_team_id',
        'auction_draft_id',
        'source',
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
     * The team whose pick brought this player into the auction.
     */
    public function nominatedBy(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'nominated_by_team_id');
    }

    /**
     * The country-draft event this lot belongs to, if nominated
     * through the draft rather than created manually by an admin.
     */
    public function draft(): BelongsTo
    {
        return $this->belongsTo(AuctionDraft::class, 'auction_draft_id');
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
