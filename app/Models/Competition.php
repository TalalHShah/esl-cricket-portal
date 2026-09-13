<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Competition extends Model
{
    /**
     * Stages a match can belong to, in bracket order. 'league' covers
     * every round-robin fixture regardless of leg.
     */
    public const STAGE_LEAGUE = 'league';
    public const STAGE_PLAYOFF_1 = 'playoff1';
    public const STAGE_PLAYOFF_2 = 'playoff2';
    public const STAGE_QUALIFIER_2 = 'qualifier2';
    public const STAGE_FINAL = 'final';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'rounds',
        'total_overs',
        'has_playoffs',
        'points_win',
        'points_tie',
        'points_loss',
        'status',
        'starts_on',
        'fixture_interval_days',
        'is_official',
    ];

    protected function casts(): array
    {
        return [
            'has_playoffs' => 'boolean',
            'is_official' => 'boolean',
            'starts_on' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Competition $competition) {
            if (! $competition->slug) {
                $competition->slug = static::uniqueSlug($competition->name);
            }
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'competition';
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'competition_teams')->withPivot('seed')->withTimestamps();
    }

    public function matches(): HasMany
    {
        return $this->hasMany(CricketMatch::class);
    }

    public function isLeague(): bool
    {
        return $this->type === 'league';
    }

    public function isCup(): bool
    {
        return $this->type === 'cup';
    }

    public function leagueMatches(): HasMany
    {
        return $this->matches()->where('stage', self::STAGE_LEAGUE);
    }

    public function playoffMatches(): HasMany
    {
        return $this->matches()->whereIn('stage', [
            self::STAGE_PLAYOFF_1, self::STAGE_PLAYOFF_2, self::STAGE_QUALIFIER_2, self::STAGE_FINAL,
        ]);
    }
}
