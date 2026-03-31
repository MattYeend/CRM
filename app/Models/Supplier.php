<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
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
 */
class Supplier extends Model
{
    /**
     * @use HasFactory<\Database\Factories\SupplierFactory>
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
     * Scope a query to only include active suppliers.
     *
     * @param  Builder<Supplier> $query The query builder instance.
     *
     * @return Builder<Supplier> The modified query builder instance.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the formatted supplier name.
     *
     * Applies a test prefix when the supplier is marked as a test record.
     *
     * @param  string|null $value The raw supplier name from the database.
     *
     * @return string The formatted supplier name.
     */
    public function getNameAttribute($value): string
    {
        return $this->prefixTest($value);
    }
}
