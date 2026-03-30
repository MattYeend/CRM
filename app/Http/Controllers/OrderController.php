<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderProducts\OrderProductManagementService;
use App\Services\Orders\OrderLogService;
use App\Services\Orders\OrderManagementService;
use App\Services\Orders\OrderQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the Order resource.
 *
 * Delegates business logic to four dedicated services:
 *   - OrderLogService — records audit log entries for order changes
 *   - OrderManagementService — handles create, update, delete, and restore
 *      operations
 *   - OrderQueryService — handles read/list queries with filtering and
 *      pagination
 *   - OrderProductManagementService — handles adding, updating, removing,
 *      and restoring products on an order
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class OrderController extends Controller
{
    /**
     * Service responsible for writing audit log entries for order events.
     *
     * @var OrderLogService
     */
    protected OrderLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * orders.
     *
     * @var OrderManagementService
     */
    protected OrderManagementService $management;

    /**
     * Service responsible for querying and listing orders.
     *
     * @var OrderQueryService
     */
    protected OrderQueryService $query;

    /**
     * Service responsible for managing the products associated with an order.
     *
     * @var OrderProductManagementService
     */
    protected OrderProductManagementService $orderProductManagement;

    /**
     * Inject the required services into the controller.
     *
     * @param  OrderLogService $logger Handles audit logging for order events.
     *
     * @param  OrderManagementService $management Handles order
     * create/update/delete/restore.
     *
     * @param  OrderQueryService $query Handles order listing and retrieval.
     *
     * @param  OrderProductManagementService $orderProductManagement Handles
     * product associations on an order.
     */
    public function __construct(
        OrderLogService $logger,
        OrderManagementService $management,
        OrderQueryService $query,
        OrderProductManagementService $orderProductManagement,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
        $this->orderProductManagement = $orderProductManagement;
    }

    /**
     * Display a listing of the resource.
     *
     * Also includes the authenticated user's permissions for the Order
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated order data.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        $order = $this->query->list($request);

        return response()->json($order);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreOrderRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param  StoreOrderRequest $request Validated request containing order
     * data.
     *
     * @return JsonResponse The newly created order, with HTTP 201 Created.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->management->store($request);

        $user = $request->user();

        $this->logger->orderCreated(
            $user,
            $user->id,
            $order,
        );

        return response()->json($order, 201);
    }

    /**
     * Display the specified resource.
     *
     * Returns a single order by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  Order $order Route-model-bound order instance.
     *
     * @return JsonResponse The resolved order resource.
     */
    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order = $this->query->show($order);

        return response()->json($order);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateOrderRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the
     * authenticated user.
     *
     * @param  UpdateOrderRequest $request Validated request containing updated
     * order data.
     *
     * @param  Order $order Route-model-bound order instance to update.
     *
     * @return JsonResponse The updated order resource.
     */
    public function update(
        UpdateOrderRequest $request,
        Order $order
    ): JsonResponse {
        $order = $this->management->update($request, $order);

        $user = $request->user();

        $this->logger->orderUpdated(
            $user,
            $user->id,
            $order
        );

        return response()->json($order);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * order instance is still fully accessible during logging.
     *
     * @param  Order $order Route-model-bound order instance to delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
     */
    public function destroy(Order $order): JsonResponse
    {
        $this->authorize('delete', $order);

        $user = auth()->user();

        $this->logger->orderDeleted(
            $user,
            $user->id,
            $order,
        );

        $this->management->destroy($order);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified order from soft deletion.
     *
     * Looks up the order including trashed records, then authorises via
     * the 'restore' policy. Returns 404 if the order is not currently
     * soft-deleted, preventing accidental double-restores.
     *
     * @param  int|string $id The primary key of the soft-deleted order.
     *
     * @return JsonResponse The restored order resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the order is not trashed (404).
     */
    public function restore(int $id): JsonResponse
    {
        $order = Order::withTrashed()->findOrFail($id);
        $this->authorize('restore', $order);

        if (! $order->trashed()) {
            abort(404);
        }

        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->orderRestored(
            $user,
            $user->id,
            $order
        );

        return response()->json($order);
    }

    /**
     * Add products to the specified order.
     *
     * Accepts a list of products from the request payload and delegates
     * to the order product management service to associate them with the
     * order.
     *
     * @param  Request $request Incoming HTTP request containing a 'products'
     * array.
     *
     * @param  Order $order Route-model-bound order instance to add products to.
     *
     * @return JsonResponse Confirmation message on success.
     */
    public function addProducts(Request $request, Order $order): JsonResponse
    {
        $items = $request->input('products');
        $this->orderProductManagement->add($order, $items);

        return response()->json(['message' => 'Products added to order']);
    }

    /**
     * Update the products associated with the specified order.
     *
     * Accepts a revised list of products from the request payload and
     * delegates to the order product management service to apply the changes.
     *
     * @param  Request $request Incoming HTTP request containing a 'products'
     * array.
     *
     * @param  Order $order Route-model-bound order instance whose products
     * should be updated.
     *
     * @return JsonResponse Confirmation message on success.
     */
    public function updateProducts(Request $request, Order $order): JsonResponse
    {
        $items = $request->input('products');
        $this->orderProductManagement->update($order, $items);

        return response()->json(['message' => 'Products updated for order']);
    }

    /**
     * Remove a product from the specified order.
     *
     * Delegates to the order product management service to dissociate the
     * given product from the order.
     *
     * @param  Order $order Route-model-bound order instance to remove the
     * product from.
     *
     * @param  Product $product Route-model-bound product instance to remove.
     *
     * @return JsonResponse Confirmation message on success.
     */
    public function removeProduct(Order $order, Product $product): JsonResponse
    {
        $this->orderProductManagement->remove($order, $product->id);

        return response()->json(['message' => 'Product removed from order']);
    }

    /**
     * Restore a previously removed product to the specified order.
     *
     * Delegates to the order product management service to re-associate the
     * given product with the order.
     *
     * @param  Order $order Route-model-bound order instance to restore the
     * product to.
     *
     * @param  Product $product Route-model-bound product instance to restore.
     *
     * @return JsonResponse Confirmation message on success.
     */
    public function restoreProduct(Order $order, Product $product): JsonResponse
    {
        $this->orderProductManagement->restore($order, $product->id);

        return response()->json(['message' => 'Product restored for order']);
    }
}
