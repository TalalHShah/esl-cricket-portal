<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    /**
     * Maximum squad size, including the team's fixed manager-player.
     */
    public const SQUAD_LIMIT = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'short_name',
        'logo',
        'primary_color',
        'secondary_color',
        'manager_id',
        'budget',
        'spent',
        'description',
        'is_active',
        'home_ground_name',
        'home_ground_location',
        'home_ground_image',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'spent' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The user who manages this team.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Players currently in this team.
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    /**
     * Players this team's manager has shortlisted for future signing —
     * a personal watchlist, independent of squad membership.
     */
    public function shortlistedPlayers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'player_shortlists')->withTimestamps();
    }

    /**
     * Matches where this team is the home side.
     */
    public function homeMatches(): HasMany
    {
        return $this->hasMany(CricketMatch::class, 'home_team_id');
    }

    /**
     * Matches where this team is the away side.
     */
    public function awayMatches(): HasMany
    {
        return $this->hasMany(CricketMatch::class, 'away_team_id');
    }

    /**
     * Bilateral/tri-series this team is (or has been) part of, whether
     * it created the series or was invited into it.
     */
    public function series(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Series::class, 'series_teams')
            ->withPivot(['status', 'responded_at'])
            ->withTimestamps();
    }

    public function createdSeries(): HasMany
    {
        return $this->hasMany(Series::class, 'created_by_team_id');
    }

    /**
     * Matches won by this team.
     */
    public function wonMatches(): HasMany
    {
        return $this->hasMany(CricketMatch::class, 'winner_team_id');
    }

    /**
     * Transfers moving players out of this team.
     */
    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'from_team_id');
    }

    /**
     * Transfers moving players into this team.
     */
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'to_team_id');
    }

    /**
     * Funds this team currently has tied up as the highest bidder on
     * one or more live/paused auction lots — locked because a bidder
     * can't retract a bid, so that money isn't free to spend elsewhere
     * (on another lot, a direct offer, or a free-agent sign) until
     * they're outbid or the lot resolves. Pass the session you're
     * currently bidding on to exclude its own prior lock from the
     * total (you're replacing that commitment, not adding to it).
     */
    public function lockedFunds(?int $excludingSessionId = null): float
    {
        return (float) AuctionSession::where('highest_bidder_team_id', $this->id)
            ->whereIn('status', ['live', 'paused'])
            ->when($excludingSessionId, fn ($q) => $q->where('id', '!=', $excludingSessionId))
            ->sum('current_bid');
    }

    /**
     * The remaining funds available to this team, after subtracting
     * both money already spent and money currently locked in live bids.
     */
    public function remainingBudget(?int $excludingSessionId = null): float
    {
        return (float) $this->budget - (float) $this->spent - $this->lockedFunds($excludingSessionId);
    }

    /**
     * Whether this team can still add another player without exceeding
     * the squad cap (which includes the team's own manager-player).
     */
    public function hasSquadSpace(): bool
    {
        return $this->players()->count() < self::SQUAD_LIMIT;
    }
}
