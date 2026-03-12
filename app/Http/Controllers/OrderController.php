<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Services\Orders\OrderLogService;
use App\Services\Orders\OrderManagementService;
use App\Services\Orders\OrderQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Declare a protected property to hold the OrderLogService,
     * OrderManagementService and OrderQueryService instance
     *
     * @var OrderLogService
     * @var OrderManagementService
     * @var OrderQueryServic
     */
    protected OrderLogService $logger;
    protected OrderManagementService $management;
    protected OrderQueryService $query;

    /**
     * Constructor for the controller
     *
     * @param OrderLogService $logger
     *
     * @param OrderManagementService $management
     *
     * @param OrderQueryService $query
     *
     * An instance of the OrderLogService used for logging
     * order-related actions
     * An instance of the OrderManagementService for management
     * of orders
     * An instance of the OrderQueryService for the query of
     * order-related actions
     */
    public function __construct(
        OrderLogService $logger,
        OrderManagementService $management,
        OrderQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
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
}
