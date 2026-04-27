<?php

namespace App\Services\Parts;

use App\Models\Part;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Handles read queries for Part records.
 *
 * Delegates sorting and trash filtering to dedicated sub-services and
 * returns paginated or single part results with all relevant relationships
 * loaded.
 */
class PartQueryService
{
    /**
     * Service responsible for applying sort order to part queries.
     *
     * @var PartSortingService
     */
    private PartSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters to part
     * queries.
     *
     * @var PartTrashFilterService
     */
    private PartTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  PartSortingService $sorting Handles sort order application.
     * @param  PartTrashFilterService $trashFilter Handles trash visibility
     * filtering.
     */
    public function __construct(
        PartSortingService $sorting,
        PartTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of parts with sorting and trash filters applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry sort, filter,
     * and pagination params.
     *
     * @return array
     */
    public function list(Request $request): array
    {
        $query = Part::with(
            'product',
            'category',
            'primarySupplier',
            'primaryImage',
            'stockMovements',
            'serialNumbers',
            'billOfMaterials',
            'usedInAssemblies'
        );

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $this->paginate($query, $request);

        return array_merge(
            $paginator,
            ['permissions' => $this->getPermissions()]
        );
    }

    /**
     * Return a single part with all relevant relationships loaded.
     *
     * @param  Part $part The route-model-bound part instance.
     *
     * @return array
     */
    public function show(Part $part): array
    {
        $part->load(
            'product',
            'category',
            'primarySupplier',
            'primaryImage',
            'stockMovements',
            'serialNumbers',
            'billOfMaterials',
            'usedInAssemblies',
        );

        return $this->formatPart($part);
    }

    /**
     * Paginate and transform the part query.
     *
     * @param  Builder $query
     * @param  Request $request
     *
     * @return array
     */
    private function paginate(Builder $query, Request $request): array
    {
        $perPage = max(1, min((int) $request->query('per_page', 10), 100));

        return $query->paginate($perPage)
            ->appends($request->query())
            ->through(fn (Part $part): array => $this->formatPart($part))
            ->toArray();
    }

    /**
     * Get top-level permission flags for the current user.
     *
     * @return array
     */
    private function getPermissions(): array
    {
        return [
            'create' => Gate::allows('create', Part::class),
            'viewAny' => Gate::allows('viewAny', Part::class),
        ];
    }

    /**
     * Format a part into a structured array.
     *
     * Combines core attributes, dimensions, pricing, stock data,
     * flags, derived values, relationships, and permissions.
     *
     * @param  Part $part
     *
     * @return array
     */
    private function formatPart(Part $part): array
    {
        return array_merge(
            $this->baseData($part),
            $this->dimensionData($part),
            $this->pricingData($part),
            $this->stockData($part),
            $this->flagData($part),
            $this->derivedData($part),
            $this->relationshipData($part),
            $this->permissionData($part),
        );
    }

    /**
     * Extract core identifying and descriptive attributes.
     *
     * @param  Part $part
     *
     * @return array
     */
    private function baseData(Part $part): array
    {
        return [
            'id' => $part->id,
            'sku' => $part->sku,
            'part_number' => $part->part_number,
            'barcode' => $part->barcode,
            'name' => $part->name,
            'description' => $part->description,
            'brand' => $part->brand,
            'manufacturer' => $part->manufacturer,
            'type' => $part->type,
            'status' => $part->status,
            'unit_of_measure' => $part->unit_of_measure,
            'colour' => $part->colour,
            'material' => $part->material,
        ];
    }

    /**
     * Extract physical dimension data.
     *
     * @param  Part $part
     *
     * @return array
     */
    private function dimensionData(Part $part): array
    {
        return [
            'height' => $part->height,
            'width' => $part->width,
            'length' => $part->length,
            'weight' => $part->weight,
            'volume' => $part->volume,
        ];
    }

    /**
     * Extract pricing and tax-related data.
     *
     * @param  Part $part
     *
     * @return array
     */
    private function pricingData(Part $part): array
    {
        return [
            'price' => $part->price,
            'cost_price' => $part->cost_price,
            'currency' => $part->currency,
            'tax_rate' => $part->tax_rate,
            'tax_code' => $part->tax_code,
            'discount_percentage' => $part->discount_percentage,
        ];
    }

    /**
     * Extract inventory and stock control data.
     *
     * @param  Part $part
     *
     * @return array
     */
    private function stockData(Part $part): array
    {
        return [
            'quantity' => $part->quantity,
            'min_stock_level' => $part->min_stock_level,
            'max_stock_level' => $part->max_stock_level,
            'reorder_point' => $part->reorder_point,
            'reorder_quantity' => $part->reorder_quantity,
            'lead_time_days' => $part->lead_time_days,
            'warehouse_location' => $part->warehouse_location,
            'bin_location' => $part->bin_location,
        ];
    }

    /**
     * Extract boolean flags describing part behaviour.
     *
     * @param  Part $part
     *
     * @return array
     */
    private function flagData(Part $part): array
    {
        return [
            'is_active' => $part->is_active,
            'is_purchasable' => $part->is_purchasable,
            'is_sellable' => $part->is_sellable,
            'is_manufactured' => $part->is_manufactured,
            'is_serialised' => $part->is_serialised,
            'is_batch_tracked' => $part->is_batch_tracked,
        ];
    }

    /**
     * Extract computed or derived attributes.
     *
     * @param  Part $part
     *
     * @return array
     */
    private function derivedData(Part $part): array
    {
        return [
            'is_low_stock' => $part->getIsLowStock(),
            'is_out_of_stock' => $part->getIsOutOfStock(),
            'margin_percentage' => $part->getMarginPercentage(),
            'has_bom' => $part->hasBom(),
        ];
    }

    /**
     * Extract related model data.
     *
     * @param  Part $part
     *
     * @return array
     */
    private function relationshipData(Part $part): array
    {
        return [
            'product' => $part->product,
            'category' => $part->category,
            'primary_supplier' => $part->primarySupplier,
            'primary_image' => $part->primaryImage,
            'stock_movements' => $part->stockMovements,
            'serial_numbers' => $part->serialNumbers,
            'bill_of_materials' => $part->billOfMaterials,
            'used_in_assemblies' => $part->usedInAssemblies,
            'creator' => $part->creator,
        ];
    }

    /**
     * Determine authorisation permissions for the part.
     *
     * @param  Part $part
     *
     * @return array
     */
    private function permissionData(Part $part): array
    {
        return [
            'permissions' => [
                'view' => Gate::allows('view', $part),
                'update' => Gate::allows('update', $part),
                'delete' => Gate::allows('delete', $part),
            ],
        ];
    }
}
