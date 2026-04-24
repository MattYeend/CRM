<?php

namespace App\Services\ProductStockMovement;

use App\Models\Product;
use App\Models\ProductStockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Handles read queries for ProductStockMovement records.
 *
 * Delegates sorting to dedicated sub-services and returns paginated stock
 * movement results, optionally scoped to a given product.
 */
class ProductStockMovementQueryService
{
    /**
     * Service responsible for applying sort order to product stock movement
     * queries.
     *
     * @var ProductStockMovementSortingService
     */
    private ProductStockMovementSortingService $sorting;

    /**
     * Inject the required services into the query service.
     *
     * @param  ProductStockMovementSortingService $sorting Handles sort order
     * application.
     */
    public function __construct(
        ProductStockMovementSortingService $sorting
    ) {
        $this->sorting = $sorting;
    }

    /**
     * Return a paginated list of stock movements, optionally scoped to a
     * given product, with sorting applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry sort and
     * pagination params.
     * @param  Product|null $product Optional product to scope movements to.
     *
     * @return array
     */
    public function list(Request $request, ?Product $product = null): array
    {
        $query = $product
            ? $product->stockMovements()->with('product')->getQuery()
            : ProductStockMovement::query()->with('product');

        $this->sorting->applySorting($query, $request);

        $paginator = $this->paginate($query, $request);

        return array_merge(
            $paginator,
            ['permissions' => $this->getPermissions()]
        );
    }

    /**
     * Return a single product stock movement with all relevant relationships
     * loaded.
     *
     * @param  ProductStockMovement $productStockMovement The route-model-bound
     * product instance.
     *
     * @return array
     */
    public function show(ProductStockMovement $productStockMovement): array
    {
        $productStockMovement->load('product', 'createdBy');

        return $this->formatProductStockMovement($productStockMovement);
    }

    /**
     * Paginate and transform the stock movement query.
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
            ->through(fn (
                ProductStockMovement $movement
            ): array => $this->formatProductStockMovement($movement))
            ->toArray();
    }

    /**
     * Get permission flags for the current user.
     *
     * @return array
     */
    private function getPermissions(): array
    {
        return [
            'create' => Gate::allows('create', ProductStockMovement::class),
            'viewAny' => Gate::allows('viewAny', ProductStockMovement::class),
        ];
    }

    /**
     * Format a product stock movement into a structured array.
     *
     * Combines core attributes, relationships, derived values,
     * metadata, and permissions into a single array.
     *
     * @param  ProductStockMovement $movement
     *
     * @return array
     */
    private function formatProductStockMovement(ProductStockMovement $movement): array
    {
        return array_merge(
            $this->baseData($movement),
            $this->quantityData($movement),
            $this->derivedData($movement),
            $this->relationshipData($movement),
            $this->permissionData($movement),
        );
    }

    /**
     * Extract core movement attributes.
     *
     * @param  ProductStockMovement $movement
     *
     * @return array
     */
    private function baseData(ProductStockMovement $movement): array
    {
        return [
            'id' => $movement->id,
            'product_id' => $movement->product_id,
            'type' => $movement->type,
            'reference' => $movement->reference,
            'notes' => $movement->notes,
        ];
    }

    /**
     * Extract quantity-related fields.
     *
     * @param  ProductStockMovement $movement
     *
     * @return array
     */
    private function quantityData(ProductStockMovement $movement): array
    {
        return [
            'quantity' => $movement->quantity,
            'quantity_before' => $movement->quantity_before,
            'quantity_after' => $movement->quantity_after,
        ];
    }

    /**
     * Extract derived movement direction flags.
     *
     * @param  ProductStockMovement $movement
     *
     * @return array
     */
    private function derivedData(ProductStockMovement $movement): array
    {
        return [
            'is_inbound' => $movement->getIsInbound(),
            'is_outbound' => $movement->getIsOutbound(),
        ];
    }

    /**
     * Extract related model data for the movement.
     *
     * @param  ProductStockMovement $movement
     *
     * @return array
     */
    private function relationshipData(ProductStockMovement $movement): array
    {
        return [
            'product' => $movement->product,
            'created_by' => $movement->createdBy,
        ];
    }

    /**
     * Determine authorisation permissions for the movement.
     *
     * @param  ProductStockMovement $movement
     *
     * @return array
     */
    private function permissionData(ProductStockMovement $movement): array
    {
        return [
            'permissions' => [
                'view' => Gate::allows('view', $movement),
                'update' => Gate::allows('update', $movement),
                'delete' => Gate::allows('delete', $movement),
            ],
        ];
    }
}
