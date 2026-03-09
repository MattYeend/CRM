<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDealRequest;
use App\Http\Requests\UpdateDealRequest;
use App\Models\Deal;
use App\Services\DealLogService;
use App\Services\DealManagementService;
use App\Services\DealQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DealController extends Controller
{
    /**
     * Declare a protected property to hold the DealLogService,
     * DealManagementService and DealQueryService instance
     *
     * @var DealLogService
     * @var DealManagementService
     * @var DealQueryService
     */
    protected DealLogService $logger;
    protected DealManagementService $managementService;
    protected DealQueryService $queryService;

    /**
     * Constructor for the controller
     *
     * @param DealLogService $logger
     *
     * @param DealManagementService $management
     *
     * @param DealQueryService $query
     *
     * An instance of the DealLogService used for logging
     * deal-related actions
     * An instance of the DealManagementService for management
     * of deals
     * An instance of the DealQueryService for the query of
     * deal-related actions
     */
    public function __construct(
        DealLogService $logger,
        DealManagementService $managementService,
        DealQueryService $queryService,
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
        $this->authorize('viewAny', Deal::class);

        $deal = $this->queryService->list($request);

        return response()->json($deal);
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

        $deal = $this->queryService->show($deal);

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
        $deal = $this->managementService->store($request);

        $user = $request->user();

        $this->logger->dealCreated(
            $user,
            $user->id,
            $deal
        );

        return response()->json($deal, 201);
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
        $deal = $this->managementService->update($request, $deal);

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

        $this->managementService->destroy($deal);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id): JsonResponse
    {
        $deal = Deal::withTrashed()->findOrFail($id);

        $this->authorize('restore', $deal);

        $user = auth()->user();

        $this->logger->dealRestored(
            $user,
            $user->id,
            $deal,
        );

        $this->managementService->restore($id);

        return response()->json($deal);
    }
}
