<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Lead;
use App\Services\Leads\LeadLogService;
use App\Services\Leads\LeadManagementService;
use App\Services\Leads\LeadQueryService;
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
    protected LeadManagementService $management;
    protected LeadQueryService $query;

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
        LeadManagementService $management,
        LeadQueryService $query,
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
        $this->authorize('viewAny', Lead::class);

        $lead = $this->query->list($request);

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
        $lead = $this->management->store($request);

        $user = $request->user();

        $this->logger->leadCreated(
            $user,
            $user->id,
            $lead,
        );

        return response()->json($lead, 201);
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

        $lead = $this->query->show($lead);

        return response()->json($lead);
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
        $lead = $this->management->update($request, $lead);

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

        $this->management->destroy($lead);

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
        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->leadRestored(
            $user,
            $user->id,
            $lead
        );

        return response()->json($lead);
    }
}
