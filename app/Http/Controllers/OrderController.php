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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Declare a protected property to hold the OrderLogService,
     * OrderManagementService, OrderQueryService and
     * OrderProductManagementService instance
     *
     * @var OrderLogService
     * @var OrderManagementService
     * @var OrderQueryService
     * @var OrderProductManagementService
     */
    protected OrderLogService $logger;
    protected OrderManagementService $management;
    protected OrderQueryService $query;
    protected OrderProductManagementService $orderProductManagement;

    /**
     * Constructor for the controller
     *
     * @param OrderLogService $logger
     *
     * @param OrderManagementService $management
     *
     * @param OrderQueryService $query
     *
     * @param OrderProductManagementService $orderProductManagement
     *
     * An instance of the OrderLogService used for logging
     * order-related actions
     * An instance of the OrderManagementService for management
     * of orders
     * An instance of the OrderQueryService for the query of
     * order-related actions
     * An instance of the OrderProductManagementService for the query of
     * order product-related actions
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
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $order = $this->query->list($request);

        return response()->json($order);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreOrderRequest $request
     *
     * @return JsonResponse
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
     * @param Order $order
     *
     * @return JsonResponse
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
     * @param UpdateOrderRequest $request
     *
     * @param Order $order
     *
     * @return JsonResponse
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
     * @param Order $order
     *
     * @return JsonResponse
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
     * Restore the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $order = Order::withTrashed()->findOrFail($id);
        $this->authorize('restore', $order);
        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->orderRestored(
            $user,
            $user->id,
            $order
        );

        return response()->json($order);
    }

    public function addProducts(Request $request, Order $order): JsonResponse
    {
        $items = $request->input('products');
        $this->orderProductManagement->add($order, $items);

        return response()->json(['message' => 'Products added to order']);
    }

    public function updateProducts(Request $request, Order $order): JsonResponse
    {
        $items = $request->input('products');
        $this->orderProductManagement->update($order, $items);

        return response()->json(['message' => 'Products updated for order']);
    }

    public function removeProduct(Order $order, Product $product): JsonResponse
    {
        $this->orderProductManagement->remove($order, $product->id);

        return response()->json(['message' => 'Product removed from order']);
    }

    public function restoreProduct(Order $order, Product $product): JsonResponse
    {
        $this->orderProductManagement->restore($order, $product->id);

        return response()->json(['message' => 'Product restored for order']);
    }
}
