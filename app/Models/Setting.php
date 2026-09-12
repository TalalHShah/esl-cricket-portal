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
     * Whether the transfer market (direct offers + free-agent scouting)
     * is currently open. The market is automatically forced closed
     * while any auction session is scheduled, live, or paused — auction
     * lots are meant to be won at auction, not signed around it — and
     * reopens on its own once no such session remains, on top of the
     * manual transfer_window.open toggle admins can also close by hand.
     */
    public static function isTransferWindowOpen(): bool
    {
        if (! static::getValue('transfer_window.open', true)) {
            return false;
        }

        return ! AuctionSession::whereIn('status', ['scheduled', 'live', 'paused'])->exists();
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
