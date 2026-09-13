<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A bilateral (2-team) or tri-series (3-team) fixture group a manager
 * sets up against other teams, distinct from the admin's official
 * round-robin Competitions. Starts as 'pending_invites' while the
 * other team(s) decide, becomes 'active' once everyone's accepted (at
 * which point fixtures can be added), and is 'completed'/'cancelled'
 * from there.
 */
class Series extends Model
{
    use HasFactory;

    protected $table = 'series';

    protected $fillable = [
        'name',
        'venue',
        'status',
        'created_by_team_id',
        'created_by_user_id',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'ended_at' => 'datetime',
        ];
    }

    public function createdByTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'created_by_team_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'series_teams')
            ->withPivot(['status', 'responded_at'])
            ->withTimestamps();
    }

    public function seriesTeams(): HasMany
    {
        return $this->hasMany(SeriesTeam::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(CricketMatch::class, 'series_id');
    }

    public function isTriSeries(): bool
    {
        return $this->seriesTeams()->count() >= 3;
    }

    public function allTeamsAccepted(): bool
    {
        return $this->seriesTeams()->where('status', '!=', 'accepted')->doesntExist();
    }

    public function anyTeamDeclined(): bool
    {
        return $this->seriesTeams()->where('status', 'declined')->exists();
    }
}
