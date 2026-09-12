<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The team managed by this user.
     */
    public function managedTeam(): HasOne
    {
        return $this->hasOne(Team::class, 'manager_id');
    }

    /**
     * Matches submitted by this user.
     */
    public function submittedMatches(): HasMany
    {
        return $this->hasMany(CricketMatch::class, 'submitted_by_user_id');
    }

    /**
     * Matches confirmed by this user.
     */
    public function confirmedMatches(): HasMany
    {
        return $this->hasMany(CricketMatch::class, 'confirmed_by_user_id');
    }

    /**
     * Screenshots uploaded by this user.
     */
    public function uploadedScreenshots(): HasMany
    {
        return $this->hasMany(MatchScreenshot::class, 'uploaded_by_user_id');
    }

    /**
     * Transfers requested by this user.
     */
    public function requestedTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'requested_by_user_id');
    }

    /**
     * Transfers approved by this user.
     */
    public function approvedTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'approved_by_user_id');
    }

    /**
     * News articles authored by this user.
     */
    public function newsArticles(): HasMany
    {
        return $this->hasMany(NewsArticle::class, 'author_id');
    }

    /**
     * Auction sessions started by this user.
     */
    public function startedAuctionSessions(): HasMany
    {
        return $this->hasMany(AuctionSession::class, 'started_by_user_id');
    }

    /**
     * Settings last updated by this user.
     */
    public function updatedSettings(): HasMany
    {
        return $this->hasMany(Setting::class, 'updated_by_user_id');
    }

    /**
     * Determine whether the user is an administrator.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
