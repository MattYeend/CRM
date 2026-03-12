<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductDealRequest;
use App\Http\Requests\UpdateProductDealRequest;
use App\Models\ProductDeal;
use App\Services\ProductDeals\ProductDealLogService;
use App\Services\ProductDeals\ProductDealManagementService;
use App\Services\ProductDeals\ProductDealQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductDealController extends Controller
{
    /**
     * Declare a protected property to hold the ProductDealLogService,
     * ProductDealManagementService and ProductDealQueryService instance
     *
     * @var ProductDealLogService
     * @var ProductDealManagementService
     * @var ProductDealQueryServic
     */
    protected ProductDealLogService $logger;
    protected ProductDealManagementService $management;
    protected ProductDealQueryService $query;

    /**
     * Constructor for the controller
     *
     * @param ProductDealLogService $logger
     *
     * @param ProductDealManagementService $management
     *
     * @param ProductDealQueryService $query
     *
     * An instance of the ProductDealLogService used for logging
     * product-related actions
     * An instance of the ProductDealManagementService for management
     * of products
     * An instance of the ProductDealQueryService for the query of
     * product-related actions
     */
    public function __construct(
        ProductDealLogService $logger,
        ProductDealManagementService $management,
        ProductDealQueryService $query,
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
     * @return JsonResponce
     */
    public function index(Request $request): JsonResponse
    {
        $product = $this->query->list($request);

        return response()->json($product);
    }

    /**
     * Display the specified resource.
     *
     * @param ProductDeal $productDeal
     *
     * @return JsonResponse
     */
    public function show(ProductDeal $productDeal): JsonResponse
    {
        $this->authorize('view', $productDeal);

        $productDeal = $this->query->show($productDeal);

        return response()->json($productDeal);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductDealRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreProductDealRequest $request)
    {
        $productDeal = $this->management->store($request);

        $user = $request->user();

        $this->logger->productDealCreated(
            $user,
            $user->id,
            $productDeal,
        );

        return response()->json($productDeal, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductDealRequest $request
     *
     * @param ProductDeal $productDeal
     *
     * @return JsonResponse
     */
    public function update(
        UpdateProductDealRequest $request,
        ProductDeal $productDeal
    ): JsonResponse {
        $productDeal = $this->management->update($request, $productDeal);

        $user = $request->user();

        $this->logger->productDealUpdated(
            $user,
            $user->id,
            $productDeal,
        );

        return response()->json($productDeal);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ProductDeal $productDeal
     *
     * @return JsonResponse
     */
    public function destroy(ProductDeal $productDeal)
    {
        $this->authorize('delete', $productDeal);

        $user = auth()->user();

        $this->logger->productDealDeleted(
            $user,
            $user->id,
            $productDeal,
        );

        $this->management->destroy($productDeal);

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
        $productDeal = ProductDeal::withTrashed()->findOrFail($id);
        $this->authorize('restore', $productDeal);
        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->productDealRestored(
            $user,
            $user->id,
            $productDeal
        );

        return response()->json($productDeal);
    }
}
