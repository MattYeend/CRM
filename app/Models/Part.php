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

class Part extends Model
{
    /**
     * @use HasFactory<\Database\Factories\PartFactory>
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
        'product_id',
        'category_id',
        'supplier_id',
        'sku',
        'part_number',
        'barcode',
        'name',
        'description',
        'brand',
        'manufacturer',
        'type',
        'status',
        'unit_of_measure',
        'height',
        'width',
        'length',
        'weight',
        'volume',
        'colour',
        'material',
        'price',
        'cost_price',
        'currency',
        'tax_rate',
        'tax_code',
        'discount_percentage',
        'quantity',
        'min_stock_level',
        'max_stock_level',
        'reorder_point',
        'reorder_quantity',
        'lead_time_days',
        'warehouse_location',
        'bin_location',
        'is_active',
        'is_purchasable',
        'is_sellable',
        'is_manufactured',
        'is_serialised',
        'is_batch_tracked',
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
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'height' => 'decimal:2',
        'width' => 'decimal:2',
        'length' => 'decimal:2',
        'weight' => 'decimal:2',
        'volume' => 'decimal:2',
        'is_active' => 'boolean',
        'is_purchasable' => 'boolean',
        'is_sellable' => 'boolean',
        'is_manufactured' => 'boolean',
        'is_serialised' => 'boolean',
        'is_batch_tracked' => 'boolean',
        'is_test' => 'boolean',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the product that the part belongs to.
     *
     * @return BelongsTo<Product,Part>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the category that the part belongs to.
     *
     * @return BelongsTo<PartCategory,Part>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PartCategory::class);
    }

    /**
     * Get the primary supplier of the part
     *
     * @return BelongsTo<Supplier,Part>
     */
    public function primarySupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Get all the suppliers of the part.
     *
     * @return BelongsToMany<Supplier>
     */
    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'part_suppliers')
            ->withPivot([
                'supplier_sku',
                'unit_cost',
                'lead_time_days',
                'is_preferred',
            ])
            ->withTimestamps();
    }

    /**
     * Get the preferred supplier via the pivot table.
     *
     * @return BelongsToMany<Supplier>
     */
    public function preferredSupplier(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'part_suppliers')
            ->using(PartSupplier::class)
            ->withPivot([
                'supplier_sku',
                'unit_cost',
                'lead_time_days',
                'is_preferred',
            ])
            ->wherePivot('is_preferred', true)
            ->withTimestamps();
    }

    /**
     * Get the image of the part.
     *
     * @return HasMany<PartImage>
     */
    public function images(): HasMany
    {
        return $this->hasMany(PartImage::class)->orderBy('sort_order');
    }

    /**
     * Get the primary image of the part.
     *
     * @return HasMany<PartImage>
     */
    public function primaryImage(): HasMany
    {
        return $this->hasMany(PartImage::class)->where('is_primary', true);
    }

    /**
     * Get all stock movements for the part.
     *
     * @return HasMany<PartStockMovement>
     */
    // public function stockMovements(): HasMany
    // {
    //     return $this->hasMany(PartStockMovement::class);
    // }

    /**
     * Get all serial numbers associated with the part.
     *
     * @return HasMany<PartSerialNumber>
     */
    // public function serialNumbers(): HasMany
    // {
    //     return $this->hasMany(PartSerialNumber::class);
    // }

    /**
     * Get the bill of materials where this part is the parent (assembly).
     *
     * @return HasMany<BillOfMaterial>
     */
    // public function billOfMaterials(): HasMany
    // {
    //     return $this->hasMany(BillOfMaterial::class, 'parent_part_id');
    // }

    /**
     * Get all assemblies where this part is used as a component.
     *
     * @return HasMany<BillOfMaterial>
     */
    // public function usedInAssemblies(): HasMany
    // {
    //     return $this->hasMany(BillOfMaterial::class, 'child_part_id');
    // }

    // Scopes
    /**
     * Scope a query to only include active parts.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to parts that are low on stock.
     *
     * Compares quantity against reorder_point.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'reorder_point');
    }

    /**
     * Scope a query to parts that are out of stock.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', 0);
    }

    // Helpers

    /**
     * Determine if the part is low on stock.
     *
     * @return bool
     */
    public function isLowStock(): bool
    {
        return $this->reorder_point && $this->quantity <= $this->reorder_point;
    }

    /**
     * Determine if the part is out of stock.
     *
     * @return bool
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity === 0;
    }

    /**
     * Calculate the profit margin percentage.
     *
     * Returns null if cost price is not set or zero.
     *
     * @return float|null
     */
    public function marginPercentage(): ?float
    {
        if (! $this->cost_price || $this->cost_price === 0) {
            return null;
        }

        return round(
            ($this->price - $this->cost_price) / $this->price * 100,
            2
        );
    }
}
