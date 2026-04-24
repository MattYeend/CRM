<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStock;
use App\Http\Requests\StoreProductStockMovementRequest;
use App\Models\Product;
use App\Models\ProductStockMovement;
use App\Services\ProductStockMovement\ProductStockMovementLogService;
use App\Services\ProductStockMovement\ProductStockMovementManagementService;
use App\Services\ProductStockMovement\ProductStockMovementQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles HTTP requests for the ProductStockMovement resource.
 *
 * Delegates business logic to three dedicated services:
 *   - ProductStockMovementLogService — records audit log entries
 *   - ProductStockMovementManagementService — handles creation of stock movements
 *   - ProductStockMovementQueryService — handles retrieval and listing
 *
 * All responses are returned as JSON, making this controller suitable
 * for API consumption by frontend clients.
 */
class ProductStockMovementController extends Controller
{
    /**
     * Service responsible for writing audit log entries for stock movement
     * events.
     *
     * @var ProductStockMovementLogService
     */
    protected ProductStockMovementLogService $logger;

    /**
     * Service responsible for creating and managing stock movements.
     *
     * @var ProductStockMovementManagementService
     */
    protected ProductStockMovementManagementService $management;

    /**
     * Service responsible for querying and listing stock movements.
     *
     * @var ProductStockMovementQueryService
     */
    protected ProductStockMovementQueryService $query;

    /**
     * Inject the required services into the controller.
     *
     * @param  ProductStockMovementLogService $logger Handles audit logging for
     * stock movements.
     * @param  ProductStockMovementManagementService $management Handles creation
     * and management of stock movements.
     * @param  ProductStockMovementQueryService $query Handles retrieval and
     * listing of stock movements.
     */
    public function __construct(
        ProductStockMovementLogService $logger,
        ProductStockMovementManagementService $management,
        ProductStockMovementQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
    }

    /**
     * Display a listing of stock movements for a given product.
     *
     * Also includes the authenticated user's permissions for the
     * Product Stock Movement resource, so the frontend can conditionally
     * render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may include filters or
     * pagination parameters.
     * @param  Product $product Route-model-bound product instance.
     *
     * @return JsonResponse Paginated stock movement data with pagination
     * metadata and permissions.
     */
    public function index(Request $request, Product $product): JsonResponse
    {
        $this->authorize('viewAny', ProductStockMovement::class);

        $productStockMovements = $this->query->list($request, $product);

        return response()->json($productStockMovements);
    }

    /**
     * Store a newly created stock movement in storage.
     *
     * Validation is handled upstream by StoreProductStockMovementRequest.
     *
     * If insufficient stock is available, a 422 response is returned.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param  StoreProductStockMovementRequest $request Validated request
     * containing stock movement data.
     * @param  Product $product Route-model-bound product instance.
     *
     * @return JsonResponse The newly created stock movement with HTTP 201
     * Created.
     */
    public function store(
        StoreProductStockMovementRequest $request,
        Product $product
    ): JsonResponse {
        try {
            $movement = $this->management->store($product, $request);
        } catch (InsufficientStock $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $user = $request->user();

        $this->logger->productStockMovementCreated($user, $user->id, $movement);

        return response()->json($movement->load('createdBy'), 201);
    }

    /**
     * Display the specified stock movement.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  Product $product Route-model-bound product instance.
     * @param  ProductStockMovement $productStockMovement Route-model-bound stock
     * movement instance.
     *
     * @return JsonResponse The resolved stock movement resource.
     */
    public function show(Product $product, ProductStockMovement $productStockMovement)
    {
        $this->authorize('view', $productStockMovement);
        $this->authorize('access', $productStockMovement);

        // Ensure the movement belongs to the part
        if ($productStockMovement->part_id !== $product->id) {
            abort(404);
        }
        $productStockMovement = $this->query->show($productStockMovement);

        return response()->json($productStockMovement);
    }
}
