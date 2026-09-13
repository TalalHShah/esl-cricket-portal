<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CricketMatch extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'matches';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'competition_id',
        'series_id',
        'match_format',
        'venue',
        'stage',
        'leg',
        'home_team_id',
        'away_team_id',
        'winner_team_id',
        'match_date',
        'status',
        'submitted_by_user_id',
        'confirmed_by_user_id',
        'summary_notes',
        'home_runs',
        'home_overs',
        'home_all_out',
        'away_runs',
        'away_overs',
        'away_all_out',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
            'home_overs' => 'decimal:1',
            'away_overs' => 'decimal:1',
            'home_all_out' => 'boolean',
            'away_all_out' => 'boolean',
        ];
    }

    /**
     * The competition (league or cup) this fixture belongs to, if any
     * — ad-hoc matches created outside a competition leave this null.
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * The bilateral/tri-series this fixture belongs to, if any —
     * mutually exclusive with competition_id in practice.
     */
    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    /**
     * The overs cap implied by this match's format, for validating
     * the scorecard. Test matches have no single-innings overs cap.
     */
    public function formatOversCap(): ?int
    {
        return match ($this->match_format) {
            'T10' => 10,
            'T20' => 20,
            'ODI' => 50,
            default => $this->competition?->total_overs,
        };
    }

    /**
     * Convert an overs value stored in cricket notation (19.4 = 19
     * overs and 4 balls, NOT 19.4 decimal overs) into the actual
     * fractional overs bowled, for run-rate arithmetic.
     */
    public static function oversToFloat(null|int|float|string $overs): float
    {
        if ($overs === null) {
            return 0.0;
        }

        $overs = (float) $overs;
        $wholeOvers = floor($overs);
        $balls = round(($overs - $wholeOvers) * 10);

        if ($balls > 5) {
            $balls = 5;
        }

        return $wholeOvers + ($balls / 6);
    }

    /**
     * Overs actually faced by a side, for NRR purposes — bumped up to
     * the competition's full quota if that side was bowled out before
     * using all of it (the standard cricket NRR convention).
     */
    public function effectiveOvers(string $side, int $competitionTotalOvers): float
    {
        $allOut = $side === 'home' ? $this->home_all_out : $this->away_all_out;
        $overs = $side === 'home' ? $this->home_overs : $this->away_overs;

        if ($allOut) {
            return (float) $competitionTotalOvers;
        }

        return self::oversToFloat($overs);
    }

    /**
     * The home team.
     */
    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    /**
     * The away team.
     */
    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    /**
     * The winning team, if a result has been recorded.
     */
    public function winnerTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }

    /**
     * The user who submitted the match for review.
     */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    /**
     * The user who confirmed the match.
     */
    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id');
    }

    /**
     * Per-player statistics recorded for this match.
     */
    public function stats(): HasMany
    {
        return $this->hasMany(MatchStat::class, 'match_id');
    }

    /**
     * Screenshots attached to this match.
     */
    public function screenshots(): HasMany
    {
        return $this->hasMany(MatchScreenshot::class, 'match_id');
    }
}
