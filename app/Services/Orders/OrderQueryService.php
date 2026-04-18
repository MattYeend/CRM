<?php

namespace App\Services\Orders;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

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
     * Permissions are merged into the top-level response array so the
     * frontend can read them as `data.permissions` without colliding
     * with the paginator's own appends mechanism.
     *
     * @param  Request $request Incoming HTTP request; may carry search,
     * sort, filter, and pagination params.
     *
     * @return array Paginated order results with top-level permissions key.
     */
    public function list(Request $request): array
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = Order::with(
            'user',
            'deal',
        );

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $query->paginate($perPage)->appends($request->query());

        return $this->transformPaginator($paginator);
    }

    /**
     * Return a single order with related data loaded.
     *
     * @param  Order $order The route-model-bound order
     * instance.
     *
     * @return array
     */
    public function show(Order $order): array
    {
        $order->load(
            'user',
            'deal',
            'products',
        );

        return $this->formatOrder($order);
    }

    /**
     * Convert the paginator to an array and merge top-level permissions.
     *
     * Permissions are added as a root-level key so the Vue frontend can
     * access them as `data.permissions` alongside `data.data`,
     * `data.current_page`, etc.
     *
     * @param  LengthAwarePaginator $paginator The paginator instance
     * containing Order models.
     *
     * @return array The transformed paginator data with permissions.
     */
    private function transformPaginator(
        LengthAwarePaginator $paginator
    ): array {
        $paginator->through(
            fn (Order $order) => $this->formatOrder($order)
        );

        $result = $paginator->toArray();

        $result['permissions'] = [
            'create' => Gate::allows('create', Order::class),
            'viewAny' => Gate::allows('viewAny', Order::class),
        ];

        return $result;
    }

    /**
     * Format an order into a structured array.
     *
     * Combines core attributes, related data, and permissions.
     *
     * @param  Order $order
     *
     * @return array
     */
    private function formatOrder(Order $order): array
    {
        return array_merge(
            $this->baseData($order),
            $this->relationshipData($order),
            $this->permissionData($order),
        );
    }

    /**
     * Extract core order attributes.
     *
     * @param  Order $order
     *
     * @return array
     */
    private function baseData(Order $order): array
    {
        return [
            'id' => $order->id,
            'status' => $order->status,
            'amount' => $order->amount,
            'currency' => $order->currency,
            'payment_method' => $order->payment_method,
            'payment_intent_id' => $order->payment_intent_id,
            'charge_id' => $order->charge_id,
            'stripe_payment_intent' => $order->stripe_payment_intent,
            'stripe_invoice_id' => $order->stripe_invoice_id,
            'paid_at' => $order->paid_at,
            'meta' => $order->meta,
        ];
    }

    /**
     * Extract related model data for the order.
     *
     * Products are included here so they are available in both the Show
     * page and the OrderProducts/Index page, which both call fetchOrder()
     * against the API show endpoint.
     *
     * @param  Order $order
     *
     * @return array
     */
    private function relationshipData(Order $order): array
    {
        return [
            'user' => $order->user,
            'deal' => $order->deal,
            'products' => $order->products,
            'creator' => $order->creator,
            'assignedTo' => $order->assigned_to,
        ];
    }

    /**
     * Determine authorisation permissions for the order.
     *
     * @param  Order $order
     *
     * @return array
     */
    private function permissionData(Order $order): array
    {
        return [
            'permissions' => [
                'view' => Gate::allows('view', $order),
                'update' => Gate::allows('update', $order),
                'delete' => Gate::allows('delete', $order),
            ],
        ];
    }
}
