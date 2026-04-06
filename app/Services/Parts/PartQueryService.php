<?php

namespace App\Services\Parts;

use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
     * Each result eager-loads the product, category, primary supplier, primary
     * image, stock movements, serial numbers, bill of materials, and assembly
     * usage relationships. The per_page value is clamped between 1 and 100.
     * All active query string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry sort, filter,
     * and pagination params.
     *
     * @return LengthAwarePaginator Paginated part results.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

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

        $paginator = $query->paginate($perPage)->appends($request->query());

        $paginator->through(
            fn (Part $part) => $this->formatPart($part)
        );

        $paginator->appends([
            'permissions' => [
                'create' => Gate::allows('create', Part::class),
                'viewAny' => Gate::allows('viewAny', Part::class),
            ],
        ]);

        return $paginator;
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
     * Format a part into a structured array.
     *
     * Includes core attributes, related data, derived helper values, and
     * authorisation permissions for the current user.
     *
     * @param  Part $part
     *
     * @return array
     */
    private function formatPart(Part $part): array
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
            'height' => $part->height,
            'width' => $part->width,
            'length' => $part->length,
            'weight' => $part->weight,
            'volume' => $part->volume,
            'colour' => $part->colour,
            'material' => $part->material,
            'price' => $part->price,
            'cost_price' => $part->cost_price,
            'currency' => $part->currency,
            'tax_rate' => $part->tax_rate,
            'tax_code' => $part->tax_code,
            'discount_percentage' => $part->discount_percentage,
            'quantity' => $part->quantity,
            'min_stock_level' => $part->min_stock_level,
            'max_stock_level' => $part->max_stock_level,
            'reorder_point' => $part->reorder_point,
            'reorder_quantity' => $part->reorder_quantity,
            'lead_time_days' => $part->lead_time_days,
            'warehouse_location' => $part->warehouse_location,
            'bin_location' => $part->bin_location,
            'is_active' => $part->is_active,
            'is_purchasable' => $part->is_purchasable,
            'is_sellable' => $part->is_sellable,
            'is_manufactured' => $part->is_manufactured,
            'is_serialised' => $part->is_serialised,
            'is_batch_tracked' => $part->is_batch_tracked,
            'is_low_stock' => $part->getIsLowStock(),
            'is_out_of_stock' => $part->getIsOutOfStock(),
            'margin_percentage' => $part->getMarginPercentage(),
            'has_bom' => $part->getHasBom(),
            'product' => $part->product,
            'category' => $part->category,
            'primary_supplier' => $part->primarySupplier,
            'primary_image' => $part->primaryImage,
            'stock_movements' => $part->stockMovements,
            'serial_numbers' => $part->serialNumbers,
            'bill_of_materials' => $part->billOfMaterials,
            'used_in_assemblies' => $part->usedInAssemblies,
            'creator' => $part->creator,
            'created_at' => $part->created_at,
            'updated_at' => $part->updated_at,
            'deleted_at' => $part->deleted_at,
            'permissions' => [
                'view' => Gate::allows('view', $part),
                'update' => Gate::allows('update', $part),
                'delete' => Gate::allows('delete', $part),
            ],
        ];
    }
}
