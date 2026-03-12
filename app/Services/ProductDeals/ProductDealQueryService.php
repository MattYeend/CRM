<?php

namespace App\Services\ProductDeals;

use App\Models\ProductDeal;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductDealQueryService
{
    private ProductDealSortingService $sorting;
    private ProductDealTrashFilterService $trashFilter;
    public function __construct(
        ProductDealSortingService $sorting,
        ProductDealTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated product, applying filters/sorting.
     *
     * @param Request $request
     *
     * @return LengthAwarePaginator
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = ProductDeal::query();

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single product deal.
     *
     * @param ProductDeal $productDeal
     *
     * @return ProductDeal
     */
    public function show(ProductDeal $productDeal): ProductDeal
    {
        return $productDeal;
    }
}
