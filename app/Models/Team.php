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
     * The remaining funds available to this team.
     */
    public function remainingBudget(): float
    {
        return (float) $this->budget - (float) $this->spent;
    }
}
