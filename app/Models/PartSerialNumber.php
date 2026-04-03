<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a uniquely identifiable serialised instance of a part.
 *
 * Tracks lifecycle state (in stock, sold, returned, scrapped),
 * manufacturing and expiry dates, and optional metadata.
 * Provides query scopes for common stock states and helper methods
 * for expiry and serial formatting.
 *
 * Serial numbers may be automatically prefixed
 * when marked as test records.
 *
 * Relationships defined in this model include:
 * - part(): Belongs-to relationship to the Part
 *      this serial number belongs to.
 * Example usage of relationships:
 * ```php
 * $serial = PartSerialNumber::find(1);
 * $part = $serial->part; // Get the associated part
 * ```
 *
 * Helper methods include:
 * - getIsExpired(): Returns true if the current date
 *      is past the expiry date.
 * - getIsExpiringSoon($days = 30): Returns true if the
 *      expiry date is within the next $days.
 * - getSerialNumberAttribute($value): Accessor that
 *      applies a test prefix to the serial number
 *      when appropriate.
 * Example usage of helper methods:
 * ```php
 * $serial = PartSerialNumber::find(1);
 * if ($serial->is_expired) {
 * // This serial number is expired
 * } elseif ($serial->is_expiring_soon) {
 * // This serial number is expiring soon
 * }
 * $formattedSerial = $serial->serial_number; // Get
 * the formatted serial number
 * ```
 *
 * Query scopes include:
 * - scopeInStock($query): Filter the query to only
 *      include serial numbers currently in stock.
 * - scopeExpiringSoon($query, $days = 30): Filter the
 *      query to only include in-stock serial numbers
 *      expiring within the next $days.
 * - scopeForPart($query, $partId): Filter the query to
 *      only include serial numbers for a specific part ID.
 * - scopeReal($query): Filter the query to only include
 *      non-test serial numbers.
 * Example usage of query scopes:
 * ```php
 * $inStockSerials = PartSerialNumber::inStock()->get();
 * // Get all in-stock serial numbers
 * $expiringSerials = PartSerialNumber::expiringSoon(15)->get();
 * // Get in-stock serial numbers expiring within 15 days
 * $partSerials = PartSerialNumber::forPart($partId)->get();
 * // Get all serial numbers for a specific part
 * $realSerials = PartSerialNumber::real()->get(); // Get all
 * non-test serial numbers
 * ```
 */
class PartSerialNumber extends Model
{
    /**
     * @use HasFactory<\Database\Factories\PartSerialNumberFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * @use HasTestPrefix<\App\Traits\HasTestPrefix>
     */
    use HasFactory,
        SoftDeletes,
        HasTestPrefix;

    /**
     * Part is available and held in stock.
     */
    public const STATUS_IN_STOCK = 'in_stock';

    /**
     * Part has been sold to a customer.
     */
    public const STATUS_SOLD = 'sold';

    /**
     * Part has been returned after a sale.
     */
    public const STATUS_RETURNED = 'returned';

    /**
     * Part has been written off and is no longer usable.
     */
    public const STATUS_SCRAPPED = 'scrapped';

    /**
     * All valid status values.
     *
     * Suitable for validation rules and filtering logic.
     */
    public const STATUSES = [
        self::STATUS_IN_STOCK,
        self::STATUS_SOLD,
        self::STATUS_RETURNED,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'part_id',
        'serial_number',
        'status',
        'batch_number',
        'manufactured_at',
        'expires_at',
        'is_test',
        'meta',
        'created_by',
        'updated_by',
        'deleted_by',
        'restored_by',
        'created_at',
        'updated_at',
        'deleted_at',
        'restored_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'meta' => 'array',
        'is_test' => 'boolean',
        'manufactured_at' => 'date',
        'expires_at' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the part this serial number belongs to.
     *
     * @return BelongsTo<Part,PartSerialNumber>
     */
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    /**
     * Determine whether this serial number's expiry date has passed.
     *
     * Returns false when no expiry date is set.
     *
     * @return bool
     */
    public function getIsExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Determine whether this serial number is approaching its expiry date.
     * Uses a default lookahead window of 30 days, but can be customized.
     * Returns false when no expiry date is set.
     *
     * @param  int $days Lookahead window in days (default 30).
     *
     * @return bool
     */
    public function getIsExpiringSoon(int $days = 30): bool
    {
        return $this->expires_at
            && $this->expires_at->isFuture()
            && $this->expires_at->lessThanOrEqualTo(now()->addDays($days));
    }

    /**
     * Get the formatted serial number.
     *
     * Applies a test prefix when the serial number is marked as a test record.
     *
     * @param  string|null $value The raw serial number from the database.
     *
     * @return string The formatted serial number.
     */
    public function getSerialNumberAttribute($value): string
    {
        return $this->prefixTest($value);
    }

    /**
     * Scope a query to only serial numbers currently held in stock.
     *
     * @param  Builder<PartSerialNumber> $query The query builder instance.
     *
     * @return Builder<PartSerialNumber> The modified query builder instance.
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_STOCK);
    }

    /**
     * Scope a query to in-stock serial numbers expiring within a given
     * number of days.
     *
     * Filters to records with a non-null expiry date that falls within
     * the specified lookahead window.
     *
     * @param  Builder<PartSerialNumber> $query The query builder instance.
     * @param  int $days Lookahead window in days (default 30).
     *
     * @return Builder<PartSerialNumber> The modified query builder instance.
     */
    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->where('status', self::STATUS_IN_STOCK)
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<=', now()->addDays($days));
    }

    /**
     * Scope a query to only include serial numbers for a specific part.
     *
     * @param  Builder<PartSerialNumber> $query The query builder instance.
     * @param  int $partId The ID of the part to filter by.
     *
     * @return Builder<PartSerialNumber> The modified query builder instance.
     */
    public function scopeForPart(Builder $query, int $partId): Builder
    {
        return $query->where('part_id', $partId);
    }

    /**
     * Scope a query to only include non-test serial numbers.
     *
     * Filters out any records where the 'is_test' flag is true, ensuring that
     * only real production data is included in the results.
     *
     * @param  Builder<PartSerialNumber> $query The query builder instance.
     *
     * @return Builder<PartSerialNumber> The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }
}
