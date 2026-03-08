<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Declare a protected property to hold the ProductLogService instance
     *
     * @var ProductLogService
     */
    protected ProductLogService $logger;

    /**
     * Constructor for the controller
     *
     * @param ProductLogService $logger
     *
     * An instance of the ProductLogService used for logging
     * product-related actions
     */
    public function __construct(ProductLogService $logger)
    {
        $this->logger = $logger;
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
        $this->authorize('viewAny', Product::class);
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        return response()->json(
            Product::paginate($perPage)
        );
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
        $user = $request->user();
        $data = $request->validated();
        $data['created_by'] = $user->id;

        $product = Product::create($data);

        $this->logger->productCreated(
            $request->user(),
            $request->user()->id,
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
        $user = $request->user();
        $data = $request->validated();
        $data['updated_by'] = $user->id;

        $product->update($data);

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

        $product->update([
            'deleted_by' => $user->id,
        ]);

        $product->delete();

        return response()->json(null, 204);
    }
}
