<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Lead;
use App\Services\LeadLogService;
use App\Services\LeadManagementService;
use App\Services\LeadQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    /**
     * Declare a protected property to hold the LeadLogService,
     * LeadManagementService and LeadQueryService instance
     *
     * @var LeadLogService
     * @var LeadManagementService
     * @var LeadQueryService
     */
    protected LeadLogService $logger;
    protected LeadManagementService $managementService;
    protected LeadQueryService $queryService;

    /**
     * Constructor for the controller
     *
     * @param LeadLogService $logger
     *
     * @param LeadManagementService $management
     *
     * @param LeadQueryService $query
     *
     * An instance of the LeadLogService used for logging
     * lead-related actions
     * An instance of the LeadManagementService for management
     * of leads
     * An instance of the LeadQueryService for the query of
     * lead-related actions
     */
    public function __construct(
        LeadLogService $logger,
        LeadManagementService $managementService,
        LeadQueryService $queryService,
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
        $this->authorize('viewAny', Lead::class);

        $lead = $this->queryService->list($request);

        return response()->json($lead);
    }

    /**
     * Display the specified resource.
     *
     * @param Lead $lead
     *
     * @return JsonResponse
     */
    public function show(Lead $lead): JsonResponse
    {
        $this->authorize('view', $lead);

        $lead = $this->queryService->show($lead);

        return response()->json($lead);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreLeadRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreLeadRequest $request): JsonResponse
    {
        $lead = $this->managementService->store($request);

        $user = $request->user();

        $this->logger->leadCreated(
            $user,
            $user->id,
            $lead,
        );

        return response()->json($lead, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateLeadRequest $request
     *
     * @param Lead $lead
     *
     * @return JsonResponse
     */
    public function update(
        UpdateLeadRequest $request,
        Lead $lead
    ): JsonResponse {
        $lead = $this->managementService->update($request, $lead);

        $user = $request->user();

        $this->logger->leadUpdated(
            $user,
            $user->id,
            $lead,
        );

        return response()->json($lead);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Lead $lead
     *
     * @return JsonResponse
     */
    public function destroy(Lead $lead): JsonResponse
    {
        $this->authorize('delete', $lead);

        $user = auth()->user();
        $this->logger->leadDeleted(
            $user,
            $user->id,
            $lead,
        );

        $lead = $this->managementService->destroy($lead);

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
        $lead = Lead::withTrashed()->findOrFail($id);
        $this->authorize('restore', $lead);

        $lead = $this->managementService->restore($id);

        $this->logger->leadRestored(
            auth()->user(),
            auth()->id(),
            $lead
        );

        return response()->json($lead);
    }
}
