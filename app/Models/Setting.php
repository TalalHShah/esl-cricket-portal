<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public',
        'updated_by_user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * The user who last updated the setting.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    /**
     * Retrieve a typed setting value by key.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'integer' => (int) $setting->value,
            'decimal' => (float) $setting->value,
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode((string) $setting->value, true),
            default => $setting->value,
        };
    }

    /**
     * The admin-controlled, season-level auction status: 'open',
     * 'announced', or 'closed'. While the auction is open or announced,
     * nobody may purchase a player outright (free-agent sign or direct
     * offer) — only shortlist them from Scouts. The transfer window can
     * only ever be open once this is 'closed'.
     */
    public static function auctionStatus(): string
    {
        return static::getValue('auction.status', 'closed');
    }

    /**
     * Set the season-level auction status. Transitioning into 'closed'
     * restarts the transfer window's recurring open/closed cycle from
     * today, per the admin-configured open_days/closed_days schedule.
     */
    public static function setAuctionStatus(string $status, ?int $updatedByUserId = null): void
    {
        $previous = static::auctionStatus();

        static::updateOrCreate(
            ['key' => 'auction.status'],
            ['value' => $status, 'type' => 'string', 'updated_by_user_id' => $updatedByUserId]
        );

        if ($status === 'closed' && $previous !== 'closed') {
            static::setValue('transfer_window.cycle_start', now()->toDateString(), 'string');
        }
    }

    /**
     * Whether the transfer market (direct offers + free-agent scouting)
     * is currently open. Gated, in order:
     *   1. The auction must be admin-marked 'closed' — while it's
     *      'open' or 'announced', nobody may purchase outright.
     *   2. No auction lot may itself be scheduled/live/paused.
     *   3. The admin's manual force-closed switch must not be set.
     *   4. The recurring open/closed cycle (admin-configured day
     *      counts, anchored to when the auction was last closed) must
     *      currently be in its "open" portion.
     */
    public static function isTransferWindowOpen(): bool
    {
        if (static::auctionStatus() !== 'closed') {
            return false;
        }

        if (AuctionSession::whereIn('status', ['scheduled', 'live', 'paused'])->exists()) {
            return false;
        }

        if (! static::getValue('transfer_window.open', true)) {
            return false;
        }

        $cycleStart = static::getValue('transfer_window.cycle_start');

        if (! $cycleStart) {
            return true;
        }

        $openDays = max(1, (int) static::getValue('transfer_window.open_days', 15));
        $closedDays = max(0, (int) static::getValue('transfer_window.closed_days', 15));
        $cycleLength = $openDays + $closedDays;

        if ($cycleLength <= 0) {
            return true;
        }

        $daysElapsed = (int) \Illuminate\Support\Carbon::parse($cycleStart)->startOfDay()->diffInDays(now()->startOfDay());
        $position = $daysElapsed % $cycleLength;

        return $position < $openDays;
    }

    /**
     * Human-readable summary of the recurring cycle's current position,
     * for the admin settings screen.
     */
    public static function transferWindowCycleSummary(): ?array
    {
        $cycleStart = static::getValue('transfer_window.cycle_start');

        if (! $cycleStart) {
            return null;
        }

        $openDays = max(1, (int) static::getValue('transfer_window.open_days', 15));
        $closedDays = max(0, (int) static::getValue('transfer_window.closed_days', 15));
        $cycleLength = $openDays + $closedDays;

        $start = \Illuminate\Support\Carbon::parse($cycleStart)->startOfDay();
        $daysElapsed = (int) $start->diffInDays(now()->startOfDay());
        $position = $cycleLength > 0 ? $daysElapsed % $cycleLength : 0;
        $isOpenPortion = $position < $openDays;

        $daysUntilChange = $isOpenPortion ? ($openDays - $position) : ($cycleLength - $position);

        return [
            'cycle_start' => $start,
            'is_open_portion' => $isOpenPortion,
            'days_until_change' => $daysUntilChange,
            'next_change_at' => now()->startOfDay()->addDays($daysUntilChange),
        ];
    }

    /**
     * Persist a setting value, creating it if needed.
     */
    public static function setValue(string $key, mixed $value, string $type = 'string'): void
    {
        $stored = is_array($value) || is_object($value) ? json_encode($value) : (string) $value;

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $stored, 'type' => $type]
        );
    }
}
