<?php

namespace App\Services\Products;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single product with related data loaded.
     *
     * @param  Product $product The route-model-bound product
     * instance.
     *
     * @return Product The product with relationships loaded.
     */
    public function show(Product $product): Product
    {
        return $product;
    }
}
