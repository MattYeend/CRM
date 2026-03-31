<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStock;
use App\Http\Requests\StorePartStockMovementRequest;
use App\Models\Part;
use App\Models\PartStockMovement;
use App\Services\PartStockMovements\PartStockMovementLogService;
use App\Services\PartStockMovements\PartStockMovementManagementService;
use App\Services\PartStockMovements\PartStockMovementQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Gate;

/**
 * Handles HTTP requests for the PartStockMovement resource.
 *
 * Delegates business logic to three dedicated services:
 *   - PartStockMovementLogService — records audit log entries
 *   - PartStockMovementManagementService — handles creation of stock movements
 *   - PartStockMovementQueryService — handles retrieval and listing
 *
 * All responses are returned as JSON, making this controller suitable
 * for API consumption by frontend clients.
 */
class PartStockMovementController extends Controller
{
    /**
     * Service responsible for writing audit log entries for stock movement
     * events.
     *
     * @var PartStockMovementLogService
     */
    protected PartStockMovementLogService $logger;

    /**
     * Service responsible for creating and managing stock movements.
     *
     * @var PartStockMovementManagementService
     */
    protected PartStockMovementManagementService $management;

    /**
     * Service responsible for querying and listing stock movements.
     *
     * @var PartStockMovementQueryService
     */
    protected PartStockMovementQueryService $query;

    /**
     * Inject the required services into the controller.
     *
     * @param  PartStockMovementLogService $logger Handles audit logging for
     * stock movements.
     * @param  PartStockMovementManagementService $management Handles creation
     * and management of stock movements.
     * @param  PartStockMovementQueryService $query Handles retrieval and
     * listing of stock movements.
     */
    public function __construct(
        PartStockMovementLogService $logger,
        PartStockMovementManagementService $management,
        PartStockMovementQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
    }

    /**
     * Display a listing of stock movements for a given part.
     *
     * Also includes the authenticated user's permissions for the
     * Part Stock Movement resource, so the frontend can conditionally
     * render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may include filters or
     * pagination parameters.
     * @param  Part $part Route-model-bound part instance.
     *
     * @return JsonResponse Paginated stock movement data with pagination
     * metadata and permissions.
     */
    public function index(Request $request, Part $part): JsonResponse
    {
        $this->authorize('viewAny', PartStockMovement::class);

        $partStockMovement = $this->query->list($request, $part);

        return response()->json($partStockMovement);
    }

    /**
     * Store a newly created stock movement in storage.
     *
     * Validation is handled upstream by StorePartStockMovementRequest.
     *
     * If insufficient stock is available, a 422 response is returned.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param  StorePartStockMovementRequest $request Validated request
     * containing stock movement data.
     * @param  Part $part Route-model-bound part instance.
     *
     * @return JsonResponse The newly created stock movement with HTTP 201
     * Created.
     */
    public function store(
        StorePartStockMovementRequest $request,
        Part $part
    ): JsonResponse {
        try {
            $movement = $this->management->store($part, $request);
        } catch (InsufficientStock $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $user = $request->user();

        $this->logger->partStockMovementCreated($user, $user->id, $movement);

        return response()->json($movement->load('createdBy'), 201);
    }

    /**
     * Display the specified stock movement.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  Part $part Route-model-bound part instance.
     * @param  PartStockMovement $partStockMovement Route-model-bound stock
     * movement instance.
     *
     * @return JsonResponse The resolved stock movement resource.
     */
    public function show(Part $part, PartStockMovement $partStockMovement)
    {
        $this->authorize('view', $partStockMovement);

        $partStockMovement = $this->query->show($partStockMovement);

        return response()->json($partStockMovement);
    }
}
