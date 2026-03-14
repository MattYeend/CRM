<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDealRequest;
use App\Http\Requests\UpdateDealRequest;
use App\Models\Deal;
use App\Models\Product;
use App\Services\DealProducts\DealProductManagementService;
use App\Services\Deals\DealLogService;
use App\Services\Deals\DealManagementService;
use App\Services\Deals\DealQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DealController extends Controller
{
    /**
     * Declare a protected property to hold the DealLogService,
     * DealManagementService, DealQueryService and
     * DealProductManagementService instance
     *
     * @var DealLogService
     * @var DealManagementService
     * @var DealQueryService
     * @var DealProductManagementService
     */
    protected DealLogService $logger;
    protected DealManagementService $management;
    protected DealQueryService $query;
    protected DealProductManagementService $dealProductManagement;

    /**
     * Constructor for the controller
     *
     * @param DealLogService $logger
     *
     * @param DealManagementService $management
     *
     * @param DealQueryService $query
     *
     * @param DealProductManagementService $dealProductManagement
     *
     * An instance of the DealLogService used for logging
     * deal-related actions
     * An instance of the DealManagementService for management
     * of deals
     * An instance of the DealQueryService for the query of
     * deal-related actions
     * An instance of the DealProductManagementService for the
     * management of deal products
     */
    public function __construct(
        DealLogService $logger,
        DealManagementService $management,
        DealQueryService $query,
        DealProductManagementService $dealProductManagement
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
        $this->dealProductManagement = $dealProductManagement;
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
        $this->authorize('viewAny', Deal::class);

        $deal = $this->query->list($request);

        return response()->json($deal);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreDealRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreDealRequest $request): JsonResponse
    {
        $deal = $this->management->store($request);

        $user = $request->user();

        $this->logger->dealCreated(
            $user,
            $user->id,
            $deal
        );

        return response()->json($deal, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Deal $deal
     *
     * @return JsonResponse
     */
    public function show(Deal $deal): JsonResponse
    {
        $this->authorize('view', $deal);

        $deal = $this->query->show($deal);

        return response()->json($deal);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateDealRequest $request
     *
     * @param Deal $deal
     *
     * @return JsonResponse
     */
    public function update(
        UpdateDealRequest $request,
        Deal $deal
    ): JsonResponse {
        $deal = $this->management->update($request, $deal);

        $user = $request->user();

        $this->logger->dealUpdated(
            $user,
            $user->id,
            $deal,
        );

        return response()->json($deal);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Deal $deal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Deal $deal): JsonResponse
    {
        $this->authorize('delete', $deal);

        $user = auth()->user();

        $this->logger->dealDeleted(
            $user,
            $user->id,
            $deal,
        );

        $this->management->destroy($deal);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function restore($id): JsonResponse
    {
        $deal = Deal::withTrashed()->findOrFail($id);
        $this->authorize('restore', $deal);
        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->dealRestored(
            $user,
            $user->id,
            $deal,
        );

        return response()->json($deal);
    }

    /**
     * Add products to a deal.
     *
     * @param Request $request The HTTP request containing 'products' array.
     *
     * @param Deal $deal The deal to which products will be attached.
     *
     * Each product in 'products' array should contain:
     * - 'product_id' (int)
     * - 'quantity' (int, optional, default 1)
     * - 'price' (float, optional, default 0)
     * - 'meta' (array, optional)
     *
     * @return JsonResponse
     */
    public function addProducts(Request $request, Deal $deal)
    {
        $items = $request->input('products');
        $this->dealProductManagement->add($deal, $items);
        return response()->json(['message' => 'Products added to deal']);
    }

    /**
     * Update products attached to a deal.
     *
     * @param Request $request The HTTP request containing 'products' array.
     *
     * @param Deal $deal The deal whose products will be updated.
     *
     * Each product in 'products' array should contain:
     * - 'product_id' (int)
     * - 'quantity' (int, optional)
     * - 'price' (float, optional)
     * - 'meta' (array, optional)
     *
     * @return void
     */
    public function updateProducts(Request $request, Deal $deal)
    {
        $items = $request->input('products');
        $this->dealProductManagement->update($deal, $items);
    }

    /**
     * Remove a product from a deal.
     *
     * @param Deal $deal The deal from which the product will be removed.
     *
     * @param Product $product The product to remove.
     *
     * @return void
     */
    public function removeProduct(Deal $deal, Product $product)
    {
        $this->dealProductManagement->remove($deal, $product->id);
    }

    /**
     * Restore a previously removed product on a deal.
     *
     * @param Deal $deal The deal where the product should be restored.
     *
     * @param Product $product The product to restore.
     *
     * @return void
     */
    public function restoreProduct(Deal $deal, Product $product)
    {
        $this->dealProductManagement->restore($deal, $product->id);
    }
}
