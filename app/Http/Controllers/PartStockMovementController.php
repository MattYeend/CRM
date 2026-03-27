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

class PartStockMovementController extends Controller
{
    /**
     * Declare a protected property to hold the PartStockMovementLogService,
     * PartStockMovementManagementService and PartStockMovementQueryService
     * instance
     *
     * @var PartStockMovementLogService
     * @var PartStockMovementManagementService
     * @var PartStockMovementQueryService
     */
    protected PartStockMovementLogService $logger;
    protected PartStockMovementManagementService $management;
    protected PartStockMovementQueryService $query;

    /**
     * Constructor for the controller
     *
     * @param PartStockMovementLogService $logger
     *
     * @param PartStockMovementManagementService $management
     *
     * @param PartStockMovementQueryService $query
     *
     * An instance of the PartStockMovementLogService used for logging
     * part stock movement-related actions
     * An instance of the PartStockMovementManagementService for management
     * of part stock movements
     * An instance of the PartStockMovementQueryService for the query of
     * part stock movement-related actions
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
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @param Part £part
     *
     * @return JsonResponse
     */
    public function index(Request $request, Part $part): JsonResponse
    {
        $this->authorize('viewAny', PartStockMovement::class);

        $partStockMovement = $this->query->list($request, $part);

        return response()->json($partStockMovement);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Part $part
     *
     * @param StorePartStockMovementRequest $request
     *
     * @return JsonResponse
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
     * Display the specified resource.
     *
     * @param PartStockMovement $partStockMovement
     *
     * @param Part $part
     *
     * @return JsonResponse
     */
    public function show(Part $part, PartStockMovement $partStockMovement)
    {
        $this->authorize('view', $partStockMovement);

        $partStockMovement = $this->query->show($partStockMovement);

        return response()->json($partStockMovement);
    }
}
