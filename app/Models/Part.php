<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
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
     * @var array<int, string>
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
     * @var array<string, string>
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PartCategory::class);
    }

    public function primarySupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

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
     * @return BelongsToMany
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

    public function images(): HasMany
    {
        return $this->hasMany(PartImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasMany
    {
        return $this->hasMany(PartImage::class)->where('is_primary', true);
    }

    // public function stockMovements(): HasMany
    // {
    //     return $this->hasMany(PartStockMovement::class);
    // }

    // public function serialNumbers(): HasMany
    // {
    //     return $this->hasMany(PartSerialNumber::class);
    // }

    // public function billOfMaterials(): HasMany
    // {
    //     return $this->hasMany(BillOfMaterial::class, 'parent_part_id');
    // }

    // public function usedInAssemblies(): HasMany
    // {
    //     return $this->hasMany(BillOfMaterial::class, 'child_part_id');
    // }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'reorder_point');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', 0);
    }

    // Helpers
    public function isLowStock(): bool
    {
        return $this->reorder_point && $this->quantity <= $this->reorder_point;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity === 0;
    }

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
