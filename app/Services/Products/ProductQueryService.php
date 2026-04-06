<?php

namespace App\Services\Products;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

/**
 * Handles read queries for Product records.
 *
 * Delegates searching, sorting, and trash filtering to dedicated
 * sub-services and returns paginated or single product results with
 * the appropriate relationships loaded.
 */
class ProductQueryService
{
    /**
     * Service responsible for applying sort order.
     *
     * @var ProductSortingService
     */
    private ProductSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters.
     *
     * @var ProductTrashFilterService
     */
    private ProductTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  ProductSortingService $sorting Handles sort order.
     * @param  ProductTrashFilterService $trashFilter Handles
     * trash filtering.
     */
    public function __construct(
        ProductSortingService $sorting,
        ProductTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of products with search, sorting,
     * and trash filters applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry search,
     * sort, filter, and pagination params.
     *
     * @return LengthAwarePaginator Paginated products item results.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = Product::query();

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $query->paginate($perPage)->appends($request->query());

        $paginator->through(
            fn (Product $product) => $this->formatProduct($product)
        );

        $paginator->appends([
            'permissions' => [
                'create' => Gate::allows('create', Product::class),
                'viewAny' => Gate::allows('viewAny', Product::class),
            ],
        ]);

        return $paginator;
    }

    /**
     * Return a single product with related data loaded.
     *
     * @param  Product $product The route-model-bound product
     * instance.
     *
     * @return array
     */
    public function show(Product $product): array
    {
        $product->load('creator');

        return $this->formatProduct($product);
    }

    /**
     * Format a product into a structured array.
     *
     * Combines core attributes, pricing, stock data,
     * derived values, relationships, and permissions.
     *
     * @param  Product $product
     *
     * @return array
     */
    private function formatProduct(Product $product): array
    {
        return array_merge(
            $this->baseData($product),
            $this->pricingData($product),
            $this->stockData($product),
            $this->derivedData($product),
            $this->relationshipData($product),
            $this->permissionData($product),
        );
    }

    /**
     * Extract core product attributes.
     *
     * @param  Product $product
     *
     * @return array
     */
    private function baseData(Product $product): array
    {
        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'description' => $product->description,
            'status' => $product->status,
            'meta' => $product->meta,
        ];
    }

    /**
     * Extract pricing-related data.
     *
     * @param  Product $product
     *
     * @return array
     */
    private function pricingData(Product $product): array
    {
        return [
            'price' => $product->price,
            'formatted_price' => $product->getFormattedPriceAttribute(),
            'currency' => $product->currency,
        ];
    }

    /**
     * Extract stock control data.
     *
     * @param  Product $product
     *
     * @return array
     */
    private function stockData(Product $product): array
    {
        return [
            'quantity' => $product->quantity,
            'min_stock_level' => $product->min_stock_level,
            'max_stock_level' => $product->max_stock_level,
            'reorder_point' => $product->reorder_point,
            'reorder_quantity' => $product->reorder_quantity,
            'lead_time_days' => $product->lead_time_days,
        ];
    }

    /**
     * Extract computed product attributes.
     *
     * @param  Product $product
     *
     * @return array
     */
    private function derivedData(Product $product): array
    {
        return [
            'is_active' => $product->getIsActiveAttribute(),
            'is_discontinued' => $product->getIsDiscontinuedAttribute(),
            'is_low_stock' => $product->getIsLowStockAttribute(),
            'is_out_of_stock' => $product->getIsOutOfStockAttribute(),
        ];
    }

    /**
     * Extract related model data.
     *
     * @param  Product $product
     *
     * @return array
     */
    private function relationshipData(Product $product): array
    {
        return [
            'creator' => $product->creator,
        ];
    }

    /**
     * Determine authorisation permissions for the product.
     *
     * @param  Product $product
     *
     * @return array
     */
    private function permissionData(Product $product): array
    {
        return [
            'permissions' => [
                'view' => Gate::allows('view', $product),
                'update' => Gate::allows('update', $product),
                'delete' => Gate::allows('delete', $product),
            ],
        ];
    }
}
