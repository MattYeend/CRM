<?php

namespace App\Models;

use App\Traits\BillOfMaterials\HasBillOfMaterials;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a physical part or component within the inventory system.
 *
 * Tracks identity, physical dimensions, pricing, stock levels, and
 * warehouse location. Supports polymorphic bill of materials relationships
 * for assembly costing, multiple supplier associations, serial number and
 * stock movement tracking, and part images.
 *
 * Part types include raw materials, finished goods, consumables, spare parts,
 * and sub-assemblies. Part statuses include active, discontinued, pending, and
 * out of stock. The model includes scopes for filtering by type, status,
 * purchasability, sellability, and manufacturing method, as well as helper
 * methods for stock level checks and margin calculations.
 *
 * Bill of Materials Support:
 * Parts can serve as the manufacturable (parent) in a BOM when is_manufactured
 * is true. The polymorphic relationship allows parts to contain other parts as
 * child components. A part cannot contain itself as a child component.
 *
 * Relationships defined in this model include:
 * - product(): The product this part belongs to (optional).
 * - category(): The category this part belongs to (optional).
 * - primarySupplier(): The primary supplier for this part (optional).
 * - suppliers(): All suppliers associated with this part, with
 *      pivot data for supplier SKU, unit cost, lead time, and
 *      preferred status.
 * - preferredSupplier(): The supplier marked as preferred for
 *      this part via the pivot table.
 * - images(): All images associated with this part, ordered
 *      by sort order.
 * - primaryImage(): The primary image for this part.
 * - stockMovements(): All stock movement records for this part.
 * - serialNumbers(): All serial numbers associated with
 *      this part (if serialised).
 * - billOfMaterials(): Polymorphic relationship to BOM entries where
 *      this part is the manufacturable (parent assembly).
 * - usedInAssemblies(): All BOM entries where this part
 *      is used as a child (component) part.
 * - creator(): The user that created this part record.
 * - updater(): The user that last updated this part record.
 * - deleter(): The user that deleted this part record
 *      (if soft-deleted).
 * - restorer(): The user that restored this part record
 *      (if soft-deleted).
 *
 * Example usage of relationships:
 * ```php
 * $part = Part::find(1);
 * $product = $part->product; // Get the associated product
 * $category = $part->category; // Get the associated category
 * $primarySupplier = $part->primarySupplier; // Get the primary supplier
 * $suppliers = $part->suppliers; // Get all associated suppliers
 * $preferredSupplier = $part->preferredSupplier; // Get the preferred supplier
 * $images = $part->images; // Get all images for the part
 * $primaryImage = $part->primaryImage; // Get the primary image
 * $stockMovements = $part->stockMovements; // Get all stock movements
 * $serialNumbers = $part->serialNumbers; // Get all serial numbers
 * $billOfMaterials = $part->billOfMaterials; // Get BOM entries
 *  (if manufactured)
 * $usedInAssemblies = $part->usedInAssemblies; // Get BOMs using this as
 *  component
 * $creator = $part->creator; // Get the user that created this part
 * ```
 *
 * Helper methods include:
 * - getIsLowStock(): Returns true if the part's quantity is at or
 *      below the reorder point.
 * - getIsOutOfStock(): Returns true if the part's quantity is zero.
 * - getMarginPercentage(): Calculates the profit margin percentage
 *      based on price and cost price.
 * - hasBom(): Returns true if this is a manufactured part that can
 *      have bill of materials entries.
 * - getSumBomLineCosts(): Calculates the total cost of all BOM components.
 * - getTotalCostAttribute(): Returns BOM cost for manufactured parts,
 *      or price for purchased parts.
 *
 * Example usage of helper methods:
 * ```php
 * $part = Part::find(1);
 * if ($part->is_low_stock) {
 *   // This part is low on stock
 * }
 * if ($part->is_out_of_stock) {
 *   // This part is out of stock
 * }
 * $margin = $part->margin_percentage; // Get the profit margin percentage
 * $hasBom = $part->hasBom(); // Check if this part can have a BOM
 * $bomCost = $part->bomCost(); // Calculate total BOM cost
 * ```
 *
 * Query scopes include:
 * - scopeActive($query): Filter to only active parts.
 * - scopeLowStock($query): Filter to parts where quantity is at or below
 *      the reorder point.
 * - scopeOutOfStock($query): Filter to parts where quantity is zero.
 * - scopeOfType($query, $type): Filter to parts of a specific type (e.g.
 *      'raw_material').
 * - scopeOfStatus($query, $status): Filter to parts of a specific status
 *      (e.g. 'active').
 * - scopePurchasable($query): Filter to parts that are marked as purchasable.
 * - scopeSellable($query): Filter to parts that are marked as sellable.
 * - scopeManufactured($query): Filter to parts that are marked as
 *      manufactured in-house.
 * - scopeSerialised($query): Filter to parts that are marked as serialised.
 * - scopeBatchTracked($query): Filter to parts that are marked as batch
 *      tracked.
 * - scopeReal($query): Filter to parts that are not marked as test data.
 * - scopeSearch($query, $term): Search parts by SKU, part number, name,
 *      or description.
 *
 * Example usage of query scopes:
 * ```php
 * $activeParts = Part::active()->get(); // Get all active parts
 * $lowStockParts = Part::lowStock()->get(); // Get all parts that are low on
 * stock
 * $outOfStockParts = Part::outOfStock()->get(); // Get all parts out of stock
 * $rawMaterials = Part::ofType(Part::RAW_MATERIAL_PART_TYPE)->get();
 * $manufacturedParts = Part::manufactured()->get(); // Get manufactured parts
 * $searchResults = Part::search('Widget')->get(); // Search for parts
 * ```
 */
class Part extends Model
{
    /**
     * @use HasFactory<\Database\Factories\PartFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * @use HasBillOfMaterials<App\Traits\BillOfMaterials\HasBillOfMaterials>
     */
    use HasFactory,
        SoftDeletes,
        HasBillOfMaterials;

    /**
     * Raw material part type.
     */
    public const RAW_MATERIAL_PART_TYPE = 'raw_material';

    /**
     * Finished good part type.
     */
    public const FINISHED_GOOD_PART_TYPE = 'finished_good';

    /**
     * Consumable part type.
     */
    public const CONSUMABLE_PART_TYPE = 'consumable';

    /**
     * Spare part type.
     */
    public const SPARE_PART_PART_TYPE = 'spare_part';

    /**
     * Sub-assembly part type.
     */
    public const SUB_ASSEMBLY_PART_TYPE = 'sub_assembly';

    /**
     * All valid part types.
     */
    public const PART_TYPES = [
        self::RAW_MATERIAL_PART_TYPE,
        self::FINISHED_GOOD_PART_TYPE,
        self::CONSUMABLE_PART_TYPE,
        self::SPARE_PART_PART_TYPE,
        self::SUB_ASSEMBLY_PART_TYPE,
    ];

    /**
     * Active part status.
     */
    public const ACTIVE_PART_STATUS = 'active';

    /**
     * Discontinued part status.
     */
    public const DISCONTINUED_PART_STATUS = 'discontinued';

    /**
     * Pending part status.
     */
    public const PENDING_PART_STATUS = 'pending';

    /**
     * Out of stock part status.
     */
    public const OUT_OF_STOCK_PART_STATUS = 'out_of_stock';

    /**
     * All valid part statuses.
     */
    public const PART_STATUSES = [
        self::ACTIVE_PART_STATUS,
        self::DISCONTINUED_PART_STATUS,
        self::PENDING_PART_STATUS,
        self::OUT_OF_STOCK_PART_STATUS,
    ];

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
     * Get the primary supplier of the part.
     *
     * @return BelongsTo<Supplier,Part>
     */
    public function primarySupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Get all suppliers associated with the part.
     *
     * Pivot data includes supplier SKU, unit cost, lead time, and preferred
     * flag.
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
     * Get the preferred supplier for the part via the pivot table.
     *
     * Filters the part_suppliers pivot to only return the supplier marked
     * as preferred.
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
     * Get all images for the part, ordered by sort order.
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
    public function stockMovements(): HasMany
    {
        return $this->hasMany(PartStockMovement::class);
    }

    /**
     * Get all serial numbers associated with the part.
     *
     * @return HasMany<PartSerialNumber>
     */
    public function serialNumbers(): HasMany
    {
        return $this->hasMany(PartSerialNumber::class);
    }

    /**
     * Get the bill of materials entries where this part is the manufacturable
     * (parent assembly).
     *
     * This polymorphic relationship is only populated when is_manufactured
     * is true.
     * Each BOM entry represents a component (child part) required to
     * manufacture this part.
     *
     * @return MorphMany<BillOfMaterial>
     */
    public function billOfMaterials(): MorphMany
    {
        return $this->morphMany(BillOfMaterial::class, 'manufacturable');
    }

    /**
     * Get all BOM entries where this part is used as a component.
     *
     * This inverse relationship shows all assemblies (parts or products) that
     * use this part as a child component.
     *
     * @return HasMany<BillOfMaterial>
     */
    public function usedInAssemblies(): HasMany
    {
        return $this->hasMany(BillOfMaterial::class, 'child_part_id');
    }

    /**
     * Get the user that created the part.
     *
     * @return BelongsTo<User,Part>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the part.
     *
     * @return BelongsTo<User,Part>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the part.
     *
     * @return BelongsTo<User,Part>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the part.
     *
     * @return BelongsTo<User,Part>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Determine if the part is low on stock.
     *
     * Returns true when a reorder point is set and the current quantity
     * is at or below it.
     *
     * @return bool
     */
    public function getIsLowStock(): bool
    {
        return $this->reorder_point && $this->quantity <= $this->reorder_point;
    }

    /**
     * Determine if the part is out of stock.
     *
     * @return bool
     */
    public function getIsOutOfStock(): bool
    {
        return $this->quantity === 0;
    }

    /**
     * Calculate the profit margin percentage for the part.
     *
     * Returns null if cost price is not set or is zero.
     *
     * @return float|null The margin as a percentage, or null if unavailable.
     */
    public function getMarginPercentage(): ?float
    {
        if (! $this->cost_price) {
            return null;
        }

        return round(
            ($this->price - $this->cost_price) / $this->price * 100,
            2
        );
    }

    /**
     * Determine whether this part can have an associated bill of materials.
     *
     * Returns true only when is_manufactured is true. Manufactured parts
     * can contain other parts as components in a BOM structure.
     *
     * @return bool
     */
    public function hasBom(): bool
    {
        return (bool) $this->is_manufactured;
    }

    /**
     * Sum the total costs of all BOM line entries for this part.
     *
     * Traverses the BOM tree to calculate the sum of all component costs.
     * The $visited array prevents infinite loops in circular BOM structures.
     *
     * @param  array<int> $visited Part IDs already visited in the current
     *  traversal, passed through to prevent circular references.
     *
     * @return float The summed cost of all BOM lines.
     */
    public function getSumBomLineCosts(array $visited): float
    {
        return $this->billOfMaterials->sum(
            fn ($bom) => $bom->totalCost($visited) ?? 0
        );
    }

    /**
     * Get the total cost of the part including BOM costs if applicable.
     *
     * For manufactured parts (those with a BOM), returns the calculated
     * BOM cost.
     * For purchased parts, returns the price directly.
     *
     * @return float|null
     */
    public function getTotalCostAttribute(): ?float
    {
        if ($this->hasBom()) {
            return $this->bomCost();
        }

        return $this->price;
    }

    /**
     * Scope a query to only include active parts.
     *
     * @param  Builder<Part> $query The query builder instance.
     *
     * @return Builder<Part> The modified query builder instance.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to parts that are low on stock.
     *
     * Compares the current quantity against the reorder point.
     *
     * @param  Builder<Part> $query The query builder instance.
     *
     * @return Builder<Part> The modified query builder instance.
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('quantity', '<=', 'reorder_point');
    }

    /**
     * Scope a query to parts that are out of stock.
     *
     * @param  Builder<Part> $query The query builder instance.
     *
     * @return Builder<Part> The modified query builder instance.
     */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('quantity', 0);
    }

    /**
     * Scope a query to only include parts of a given type.
     *
     * @param  Builder<Part> $query The query builder instance.
     * @param  string $type  The part type to filter by (e.g. 'raw_material',
     *                       'finished_good').
     *
     * @return Builder<Part> The modified query builder instance.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include parts of a given status.
     *
     * @param  Builder<Part> $query The query builder instance.
     * @param  string $status  The part status to filter by (e.g. 'active',
     *                         'discontinued').
     *
     * @return Builder<Part> The modified query builder instance.
     */
    public function scopeOfStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include parts that are purchasable.
     *
     * Filters to parts where 'is_purchasable' is true, indicating
     * they can be sourced from suppliers and included in purchase
     * orders.
     *
     * @param  Builder<Part> $query The query builder instance.
     *
     * @return Builder<Part> The modified query builder instance.
     */
    public function scopePurchasable(Builder $query): Builder
    {
        return $query->where('is_purchasable', true);
    }

    /**
     * Scope a query to only include parts that are sellable.
     *
     * Filters to parts where 'is_sellable' is true, indicating
     * they can be sold to customers and included in sales orders.
     *
     * @param  Builder<Part> $query The query builder instance.
     *
     * @return Builder<Part> The modified query builder instance.
     */
    public function scopeSellable(Builder $query): Builder
    {
        return $query->where('is_sellable', true);
    }

    /**
     * Scope a query to only include parts that are manufactured in-house.
     *
     * Filters to parts where 'is_manufactured' is true, indicating
     * they are produced internally rather than sourced from suppliers.
     * Useful for differentiating between raw materials, sub-assemblies,
     * and finished goods in inventory management and production planning
     * contexts.
     *
     * @param  Builder<Part> $query The query builder instance.
     *
     * @return Builder<Part> The modified query builder instance.
     */
    public function scopeManufactured(Builder $query): Builder
    {
        return $query->where('is_manufactured', true);
    }

    /**
     * Scope a query to only include parts that are serialised.
     *
     * Filters to parts where 'is_serialised' is true, indicating
     * that individual units of the part are tracked with unique
     * serial numbers. Useful for managing warranty, service, and
     * traceability requirements for high-value or regulated items.
     *
     * @param  Builder<Part> $query The query builder instance.
     *
     * @return Builder<Part> The modified query builder instance.
     */
    public function scopeSerialised(Builder $query): Builder
    {
        return $query->where('is_serialised', true);
    }

    /**
     * Scope a query to only include parts that are batch tracked.
     *
     * Filters to parts where 'is_batch_tracked' is true, indicating
     * that inventory is managed in batches or lots rather than
     * individual units. Useful for managing expiry, quality control,
     * and traceability requirements for items like chemicals,
     * pharmaceuticals, or food products.
     *
     * @param  Builder<Part> $query The query builder instance.
     *
     * @return Builder<Part> The modified query builder instance.
     */
    public function scopeBatchTracked(Builder $query): Builder
    {
        return $query->where('is_batch_tracked', true);
    }

    /**
     * Scope a query to only include non-test parts.
     *
     * Filters out any records where the 'is_test' flag is true, ensuring
     * that only real production data is included in the results.
     * Important for accurate inventory reporting, cost analysis, and
     * production planning by excluding any parts that are created for
     * testing purposes and not actually used in the manufacturing or
     * sales processes.
     *
     * @param  Builder<Part> $query The query builder instance.
     *
     * @return Builder<Part> The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }

    /**
     * Scope a search query to filter parts by a search term.
     *
     * Filters parts where the SKU, part number, name, or description
     * contains the search term. Useful for implementing search functionality
     * in the UI, allowing users to quickly find parts based on common
     * identifiers or keywords.
     * The search is case-insensitive and matches partial terms, making it
     * flexible for finding relevant parts even with incomplete information.
     *
     * @param  Builder<Part> $query The query builder instance.
     * @param  string $term  The search term to filter by.
     *
     * @return Builder<Part> The modified query builder instance.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%' . $term . '%';

        return $query->where(function ($q) use ($like) {
            $q->where('sku', 'like', $like)
                ->orWhere('part_number', 'like', $like)
                ->orWhere('name', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }
}
