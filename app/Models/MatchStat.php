<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchStat extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'match_id',
        'player_id',
        'team_id',
        'runs_scored',
        'balls_faced',
        'fours',
        'sixes',
        'overs_bowled',
        'maidens',
        'runs_conceded',
        'wickets_taken',
        'catches',
        'stumpings',
        'was_exceptional',
        'value_change',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'overs_bowled' => 'decimal:1',
            'value_change' => 'decimal:2',
            'was_exceptional' => 'boolean',
        ];
    }

    /**
     * The match this statistic belongs to.
     */
    public function match(): BelongsTo
    {
        return $this->belongsTo(CricketMatch::class, 'match_id');
    }

    /**
     * The player these statistics describe.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * The team the player represented in this match.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Total runs contributed by batting and bowling (fantasy points helper).
     */
    public function impactScore(): int
    {
        return ($this->runs_scored + ($this->wickets_taken * 20) + ($this->catches * 10) + ($this->stumpings * 10));
    }
}
