<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Builder;
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
 *
 * Relationships defined in this model include:
 * - deal(): Belongs-to relationship to the Deal this quote belongs to.
 * - products(): Many-to-many relationship to Product records via the
 *      quote_products pivot table, including line-item quantity, price,
 *      and total.
 * - creator(): Belongs-to relationship to the User who created the quote.
 * - updater(): Belongs-to relationship to the User who last updated the
 *      quote.
 * - deleter(): Belongs-to relationship to the User who deleted the quote
 *      (if soft-deleted).
 * - restorer(): Belongs-to relationship to the User who restored the quote
 *      (if soft-deleted).
 * Example usage of relationships:
 * ```php
 * $quote = Quote::find(1);
 * $deal = $quote->deal; // Get the associated deal
 * $products = $quote->products; // Get all products with line-item pivot data
 * $creator = $quote->creator; // Get the user that created the quote
 * $updater = $quote->updater; // Get the user that last updated the quote
 * $deleter = $quote->deleter; // Get the user that deleted the quote
 * (if applicable)
 * $restorer = $quote->restorer; // Get the user that restored the quote
 * (if applicable)
 * ```
 *
 * Accessor methods include:
 * - getIsSentAttribute(): Returns a boolean indicating whether the quote
 *      has been sent to the customer.
 * - getIsAcceptedAttribute(): Returns a boolean indicating whether the
 *      quote has been accepted.
 * - getFormattedSubtotalAttribute(): Returns the subtotal formatted to
 *      two decimal places as a string.
 * - getFormattedTaxAttribute(): Returns the tax amount formatted to two
 *      decimal places as a string.
 * - getFormattedTotalAttribute(): Returns the total formatted to two
 *      decimal places as a string.
 * Example usage of accessors:
 * ```php
 * $quote = Quote::find(1);
 * $isSent = $quote->is_sent; // Check if the quote has been sent
 * $isAccepted = $quote->is_accepted; // Check if the quote has been accepted
 * $subtotal = $quote->formatted_subtotal; // e.g. "1200.00"
 * $total = $quote->formatted_total; // e.g. "1440.00"
 * ```
 *
 * Query scopes include:
 * - scopeSent($query): Filter the query to only include quotes that have
 *      been sent.
 * - scopeUnsent($query): Filter the query to only include quotes that
 *      have not yet been sent.
 * - scopeAccepted($query): Filter the query to only include quotes that
 *      have been accepted.
 * - scopePending($query): Filter the query to only include quotes that
 *      have been sent but not yet accepted.
 * - scopeForDeal($query, $dealId): Filter the query to only include
 *      quotes for a given deal.
 * - scopeInCurrency($query, $currency): Filter the query to only include
 *      quotes in a given currency.
 * - scopeReal($query): Filter the query to only include non-test quotes.
 * Example usage of query scopes:
 * ```php
 * $sent = Quote::sent()->get(); // Get all sent quotes
 * $accepted  = Quote::accepted()->get(); // Get all accepted quotes
 * $pending = Quote::pending()->get(); // Sent but not yet accepted
 * $dealQuotes = Quote::forDeal($dealId)->get(); // Quotes for a specific deal
 * $gbpQuotes  = Quote::inCurrency('GBP')->get(); // Quotes in GBP
 * $real = Quote::real()->get(); // Exclude test records
 * ```
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
        'subtotal' => 'float',
        'tax' => 'float',
        'total' => 'float',
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

    /**
     * Determine whether the quote has been sent to the customer.
     *
     * Returns true when the 'sent_at' timestamp is populated, indicating
     * that the quote has been dispatched. Useful for conditional display
     * logic and filtering in quote list views.
     *
     * @return bool
     */
    public function getIsSentAttribute(): bool
    {
        return $this->sent_at !== null;
    }

    /**
     * Determine whether the quote has been accepted by the customer.
     *
     * Returns true when the 'accepted_at' timestamp is populated,
     * indicating that the customer has confirmed the quote. Useful for
     * triggering downstream actions such as invoice creation.
     *
     * @return bool
     */
    public function getIsAcceptedAttribute(): bool
    {
        return $this->accepted_at !== null;
    }

    /**
     * Get the subtotal formatted to two decimal places.
     *
     * Returns the subtotal as a string without currency symbols.
     * Pair with the quote's currency field for a fully formatted value
     * suitable for display or export.
     *
     * @return string
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return number_format((float) $this->subtotal, 2, '.', '');
    }

    /**
     * Get the tax amount formatted to two decimal places.
     *
     * Returns the tax as a string without currency symbols.
     * Pair with the quote's currency field for a fully formatted value
     * suitable for display or export.
     *
     * @return string
     */
    public function getFormattedTaxAttribute(): string
    {
        return number_format((float) $this->tax, 2, '.', '');
    }

    /**
     * Get the total formatted to two decimal places.
     *
     * Returns the total as a string without currency symbols.
     * Pair with the quote's currency field for a fully formatted value
     * suitable for display or export.
     *
     * @return string
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format((float) $this->total, 2, '.', '');
    }

    /**
     * Scope a query to only include quotes that have been sent.
     *
     * Filters by the presence of a 'sent_at' timestamp. Useful for
     * reporting on dispatched quotes or tracking customer-facing activity
     * within a given period.
     *
     * @param  Builder<Quote> $query    query builder instance.
     *
     * @return Builder<Quote> The modified query builder instance.
     */
    public function scopeSent(Builder $query): Builder
    {
        return $query->whereNotNull('sent_at');
    }

    /**
     * Scope a query to only include quotes that have not yet been sent.
     *
     * Filters by the absence of a 'sent_at' timestamp. Useful for
     * identifying draft quotes that are awaiting review or dispatch.
     *
     * @param  Builder<Quote> $query The query builder instance.
     *
     * @return Builder<Quote> The modified query builder instance.
     */
    public function scopeUnsent(Builder $query): Builder
    {
        return $query->whereNull('sent_at');
    }

    /**
     * Scope a query to only include quotes that have been accepted.
     *
     * Filters by the presence of an 'accepted_at' timestamp. Useful for
     * identifying won opportunities and triggering invoice creation
     * workflows.
     *
     * @param  Builder<Quote> $query The query builder instance.
     *
     * @return Builder<Quote> The modified query builder instance.
     */
    public function scopeAccepted(Builder $query): Builder
    {
        return $query->whereNotNull('accepted_at');
    }

    /**
     * Scope a query to only include quotes that have been sent but not
     * yet accepted.
     *
     * Combines the sent and unaccepted conditions to surface quotes that
     * are awaiting a customer decision. Useful for follow-up workflows
     * and pipeline reporting.
     *
     * @param  Builder<Quote> $query The query builder instance.
     *
     * @return Builder<Quote> The modified query builder instance.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereNotNull('sent_at')->whereNull('accepted_at');
    }

    /**
     * Scope a query to only include quotes for a given deal.
     *
     * Filters by the 'deal_id' column. Useful for loading all quotes
     * associated with a specific deal, for example when displaying a
     * deal's quote history or selecting the most recent quote.
     *
     * @param  Builder<Quote> $query The query builder instance.
     * @param  int $dealId The ID of the deal to filter by.
     *
     * @return Builder<Quote> The modified query builder instance.
     */
    public function scopeForDeal(
        Builder $query,
        int $dealId
    ): Builder {
        return $query->where('deal_id', $dealId);
    }

    /**
     * Scope a query to only include quotes in a given currency or list
     * of currencies.
     *
     * Accepts either a single currency code as a string or an array of
     * currency codes. Applies a where or whereIn clause accordingly.
     * Useful for multi-currency reporting or filtering quotes for a
     * specific billing context.
     *
     * @param  Builder<Quote> $query The query builder instance.
     * @param  string|array<int,string> $   The currency code or
     * codes to filter by.
     *
     * @return Builder<Quote> The modified query builder instance.
     */
    public function scopeInCurrency(
        Builder $query,
        string|array $currency
    ): Builder {
        return is_array($currency)
            ? $query->whereIn('currency', $currency)
            : $query->where('currency', $currency);
    }

    /**
     * Scope a query to only include non-test quotes.
     *
     * Filters out any quote records where the 'is_test' flag is true,
     * ensuring that queries return only real quote records. Important
     * for accurate financial reporting and deal valuations.
     *
     * @param  Builder<Quote> $query The query builder instance.
     *
     * @return Builder<Quote> The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }
}
