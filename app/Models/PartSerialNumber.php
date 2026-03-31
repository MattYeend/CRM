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
 * Tracks lifecycle state (in stock, sold, returned, scrapped), manufacturing
 * and expiry dates, and optional metadata. Provides query scopes for common
 * stock states and helper methods for expiry and serial formatting.
 *
 * Serial numbers may be automatically prefixed when marked as test records.
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
     * Determine whether this serial number's expiry date has passed.
     *
     * Returns false when no expiry date is set.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
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
}
