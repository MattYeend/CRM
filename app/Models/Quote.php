<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a commercial quote associated with a deal.
 *
 * Quotes capture pricing details for a set of products, including
 * subtotal, tax, and total amounts. They track lifecycle events such
 * as when the quote was sent and when it was accepted.
 *
 * Quotes are linked to deals and products, and support audit tracking
 * (created, updated, deleted, restored). They may also be marked as
 * test records, in which case certain attributes may be prefixed.
 */
class Quote extends Model
{
    /**
     * @use HasFactory<\Database\Factories\QuoteFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * @use HasTestPrefix<\App\Traits\HasTestPrefix>
     */
    use HasFactory,
        SoftDeletes,
        HasTestPrefix;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'deal_id',
        'currency',
        'subtotal',
        'tax',
        'total',
        'sent_at',
        'accepted_at',
        'is_test',
        'meta',
        'created_by',
        'updated_by',
        'deleted_by',
        'restored_by',
        'restored_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'is_test' => 'boolean',
        'meta' => 'array',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the deal this quote belongs to.
     *
     * @return BelongsTo<Deal,Quote>
     */
    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    /**
     * Get the user that created the quote.
     *
     * @return BelongsTo<User,Quote>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the quote.
     *
     * @return BelongsTo<User,Quote>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the quote.
     *
     * @return BelongsTo<User,Quote>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the quote.
     *
     * @return BelongsTo<User,Quote>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get the products associated with the quote.
     *
     * Includes pivot data such as quantity, price, and total.
     *
     * @return BelongsToMany<Product>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'quote_products')
            ->using(QuoteProduct::class)
            ->withPivot(['quantity', 'price', 'total'])
            ->withTimestamps();
    }
}
