<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductLogService;
use App\Services\ProductManagementService;
use App\Services\ProductQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Declare a protected property to hold the ProductLogService,
     * ProductManagementService and ProductQueryService instance
     *
     * @var ProductLogService
     * @var ProductManagementService
     * @var ProductQueryServic
     */
    protected ProductLogService $logger;
    protected ProductManagementService $managementService;
    protected ProductQueryService $queryService;

    /**
     * Constructor for the controller
     *
     * @param ProductLogService $logger
     *
     * @param ProductManagementService $management
     *
     * @param ProductQueryService $query
     *
     * An instance of the ProductLogService used for logging
     * product-related actions
     * An instance of the ProductManagementService for management
     * of products
     * An instance of the ProductQueryService for the query of
     * product-related actions
     */
    public function __construct(
        ProductLogService $logger,
        ProductManagementService $managementService,
        ProductQueryService $queryService,
    ) {
        $this->logger = $logger;
        $this->managementService = $managementService;
        $this->queryService = $queryService;
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
        $product = $this->queryService->list($request);

        return response()->json($product);
    }

    /**
     * Display the specified resource.
     *
     * @param Product $product
     *
     * @return JsonResponse
     */
    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);

        $product = $this->queryService->show($product);

        return response()->json($product);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->managementService->store($request);

        $user = $request->user();

        $this->logger->productCreated(
            $user,
            $user->id,
            $product,
        );

        return response()->json($product, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductRequest $request
     *
     * @param Product $product
     *
     * @return JsonResponse
     */
    public function update(
        UpdateProductRequest $request,
        Product $product
    ): JsonResponse {
        $product = $this->managementService->update($request, $product);

        $user = $request->user();

        $this->logger->productUpdated(
            $user,
            $user->id,
            $product,
        );

        return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Product $product
     *
     * @return JsonResponse
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $user = auth()->user();

        $this->logger->productDeleted(
            $user,
            $user->id,
            $product,
        );

        $product = $this->managementService->destroy($product);

        return response()->json(null, 204);
    }
}
