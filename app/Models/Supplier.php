<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a supplier providing parts or products.
 *
 * Suppliers store contact, address, and commercial information such as
 * payment terms, tax details, and preferred currency. They are linked
 * to parts via a many-to-many relationship that includes supplier-specific
 * data (e.g. supplier SKU, unit cost, lead time).
 *
 * Suppliers support lifecycle tracking (active/inactive), audit fields,
 * and may be marked as test records with prefixed attributes.
 *
 * Relationships defined in this model include:
 * - parts(): Many-to-many relationship to Part records supplied by this
 *      supplier, with pivot data including supplier SKU, unit cost, lead
 *      time, and preferred supplier flag.
 * - partSuppliers(): One-to-many relationship to PartSupplier pivot
 *      records for direct access to supplier-part link data.
 * - creator(): Belongs-to relationship to the User who created the
 *      supplier record.
 * - updater(): Belongs-to relationship to the User who last updated the
 *      supplier record.
 * - deleter(): Belongs-to relationship to the User who deleted the
 *      supplier record (if soft-deleted).
 * - restorer(): Belongs-to relationship to the User who restored the
 *      supplier record (if soft-deleted).
 * Example usage of relationships:
 * ```php
 * $supplier = Supplier::find(1);
 * $parts = $supplier->parts; // Get all parts for the supplier
 * $pivotRecords = $supplier->partSuppliers; // Get raw pivot records
 * $creator = $supplier->creator; // Get the user that created the record
 * $updater = $supplier->updater; // Get the user that last updated the record
 * $deleter = $supplier->deleter; // Get the user that deleted the record
 * (if applicable)
 * $restorer = $supplier->restorer; // Get the user that restored the record
 * (if applicable)
 * ```
 *
 * Accessor methods include:
 * - getNameAttribute(): Returns the supplier name, applying a test prefix
 *      if the supplier is marked as a test record.
 * - getFullAddressAttribute(): Returns the supplier's full postal address
 *      as a single formatted string.
 * - getWebsiteHostAttribute(): Returns the host portion of the supplier's
 *      website URL without the scheme or www prefix, or null if not set.
 * Example usage of accessors:
 * ```php
 * $supplier = Supplier::find(1);
 * $name = $supplier->name; // Get the name with test prefix if applicable
 * $address = $supplier->full_address; // Get the formatted postal address
 * $websiteHost = $supplier->website_host; // Get the bare domain, e.g.
 * "example.com"
 * ```
 *
 * Query scopes include:
 * - scopeActive($query): Filter the query to only include active suppliers.
 * - scopeInactive($query): Filter the query to only include inactive
 *      suppliers.
 * - scopeInCountry($query, $country): Filter the query to only include
 *      suppliers in a given country or list of countries.
 * - scopeInCurrency($query, $currency): Filter the query to only include
 *      suppliers operating in a given currency or list of currencies.
 * - scopeReal($query): Filter the query to only include non-test suppliers.
 * - scopeSearch($query, $term): Filter the query by name, contact name,
 *      or contact email using a single search term.
 * Example usage of query scopes:
 * ```php
 * $active = Supplier::active()->get(); // Get active suppliers
 * $ukSuppliers = Supplier::inCountry('GB')->get(); // Get UK suppliers
 * $gbpSuppliers = Supplier::inCurrency('GBP')->get(); // Get GBP suppliers
 * $results = Supplier::search('Acme')->get(); // Search by name
 * $real = Supplier::real()->get(); // Exclude test records
 * ```
 */
class Supplier extends Model
{
    /**
     * @use HasFactory<\Database\Factories\SupplierFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     */
    use HasFactory,
        SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
        'code',
        'email',
        'phone',
        'website',
        'address_line_1',
        'address_line_2',
        'city',
        'county',
        'postcode',
        'country',
        'currency',
        'payment_terms',
        'tax_number',
        'contact_name',
        'contact_email',
        'contact_phone',
        'is_active',
        'notes',
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
        'is_active' => 'boolean',
        'is_test' => 'boolean',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the parts supplied by this supplier.
     *
     * Includes pivot data such as supplier SKU, unit cost, lead time,
     * and preferred supplier flag.
     *
     * @return BelongsToMany<Part>
     */
    public function parts(): BelongsToMany
    {
        return $this->belongsToMany(Part::class, 'part_suppliers')
            ->withPivot([
                'supplier_sku',
                'unit_cost',
                'lead_time_days',
                'is_preferred',
            ])
            ->withTimestamps();
    }

    /**
     * Get the part-supplier pivot records for this supplier.
     *
     * @return HasMany<PartSupplier>
     */
    public function partSuppliers(): HasMany
    {
        return $this->hasMany(PartSupplier::class);
    }

    /**
     * Get the user that created the supplier.
     *
     * @return BelongsTo<User,Supplier>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the supplier.
     *
     * @return BelongsTo<User,Supplier>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the supplier.
     *
     * @return BelongsTo<User,Supplier>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the supplier.
     *
     * @return BelongsTo<User,Supplier>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get the formatted supplier name.
     *
     * Applies a test prefix when the supplier is marked as a test record.
     *
     * @param  string|null  $value  The raw supplier name from the database.
     *
     * @return string The formatted supplier name.
     */
    public function getNameAttribute($value): string
    {
        return $this->prefixTest($value);
    }

    /**
     * Get the supplier's full postal address as a single formatted string.
     *
     * Combines address lines, city, county, postcode, and country into a
     * single string. Filters out any empty components to avoid extra commas
     * in the output. Useful for displaying a clean address in the UI without
     * manual formatting on each use.
     *
     * @return string
     */
    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->county,
            $this->postcode,
            $this->country,
        ])->filter()->implode(', ');
    }

    /**
     * Get the supplier's website host without the scheme or www prefix,
     * returning null when no website is set.
     *
     * Parses the website URL to extract the host component, stripping any
     * leading 'www.' for a cleaner display value. Returns null if the
     * website attribute is blank or cannot be parsed.
     *
     * @return string|null
     */
    public function getWebsiteHostAttribute(): ?string
    {
        if (blank($this->website)) {
            return null;
        }

        $host = parse_url($this->website, PHP_URL_HOST);

        return $host ? ltrim($host, 'www.') : null;
    }

    /**
     * Scope a query to only include active suppliers.
     *
     * Filters the query to include only suppliers where the 'is_active'
     * attribute is true. Useful for ensuring that inactive or discontinued
     * suppliers are excluded from procurement and reporting queries.
     *
     * @param  Builder<Supplier>  $query  The query builder instance.
     *
     * @return Builder<Supplier> The modified query builder instance.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive suppliers.
     *
     * Filters the query to include only suppliers where the 'is_active'
     * attribute is false. Useful for auditing or reactivating suppliers
     * that have been previously deactivated.
     *
     * @param  Builder<Supplier> $query The query builder instance.
     *
     * @return Builder<Supplier> The modified query builder instance.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope a query to only include suppliers in a given country or
     * list of countries.
     *
     * Accepts either a single country value as a string or multiple
     * countries as an array of strings. Applies a where or whereIn
     * clause accordingly. Country values should match the format
     * stored in the database (e.g. ISO codes or full names).
     *
     * @param  Builder<Supplier> $query The query builder instance.
     * @param  string|array<int,string> $country The country or
     * countries to filter by.
     *
     * @return Builder<Supplier> The modified query builder instance.
     */
    public function scopeInCountry(
        Builder $query,
        string|array $country
    ): Builder {
        return is_array($country)
            ? $query->whereIn('country', $country)
            : $query->where('country', $country);
    }

    /**
     * Scope a query to only include suppliers operating in a given
     * currency or list of currencies.
     *
     * Accepts either a single currency code as a string or multiple
     * currency codes as an array. Applies a where or whereIn clause
     * accordingly. Useful for filtering suppliers when working in a
     * specific billing or reporting currency context.
     *
     * @param  Builder<Supplier> $query The query builder instance.
     * @param  string|array<int,string> $currency The currency code
     * or codes to filter by.
     *
     * @return Builder<Supplier> The modified query builder instance.
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
     * Scope a query to only include non-test suppliers.
     *
     * Filters the query to exclude any supplier records where the
     * 'is_test' flag is true, ensuring that queries return only real
     * supplier records in the system.
     *
     * @param  Builder<Supplier> $query The query builder instance.
     *
     * @return Builder<Supplier> The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }

    /**
     * Scope a query to search suppliers by name, contact name, or
     * contact email using a single search term.
     *
     * Wraps the conditions in a grouped where clause to ensure correct
     * boolean precedence when chained with other scopes. Useful for
     * powering search inputs in supplier lists or lookup fields.
     *
     * @param  Builder<Supplier> $query  The query builder instance.
     * @param  string $term The search term to match against.
     *
     * @return Builder<Supplier> The modified query builder instance.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = "%{$term}%";

        return $query->where(function (Builder $q) use ($like) {
            $q->where('name', 'like', $like)
                ->orWhere('contact_name', 'like', $like)
                ->orWhere('contact_email', 'like', $like)
                ->orWhere('code', 'like', $like);
        });
    }
}
