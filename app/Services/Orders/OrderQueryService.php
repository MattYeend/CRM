<?php

namespace App\Services\Orders;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Handles read queries for Order records.
 *
 * Delegates searching, sorting, and trash filtering to dedicated
 * sub-services and returns paginated or single order results with
 * the appropriate relationships loaded.
 */
class OrderQueryService
{
    /**
     * Service responsible for applying sort order.
     *
     * @var OrderSortingService
     */
    private OrderSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters.
     *
     * @var OrderTrashFilterService
     */
    private OrderTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  OrderSortingService $sorting Handles sort order.
     * @param  OrderTrashFilterService $trashFilter Handles
     * trash filtering.
     */
    public function __construct(
        OrderSortingService $sorting,
        OrderTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of orders with search, sorting,
     * and trash filters applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry search,
     * sort, filter, and pagination params.
     *
     * @return LengthAwarePaginator Paginated orders item results.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = Order::query();

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single order with related data loaded.
     *
     * @param  Order $order The route-model-bound order
     * instance.
     *
     * @return Order The order with relationships loaded.
     */
    public function show(Order $order): Order
    {
        return $order;
    }
}
