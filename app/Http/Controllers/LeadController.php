<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Lead;
use App\Services\Leads\LeadLogService;
use App\Services\Leads\LeadManagementService;
use App\Services\Leads\LeadQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the Lead resource.
 *
 * Delegates business logic to three dedicated services:
 *   - LeadLogService — records audit log entries for lead changes
 *   - LeadManagementService — handles create, update, delete, and restore
 *      operations
 *   - LeadQueryService — handles read/list queries with filtering and
 *      pagination
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class LeadController extends Controller
{
    /**
     * Service responsible for writing audit log entries for lead events.
     *
     * @var LeadLogService
     */
    protected LeadLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * leads.
     *
     * @var LeadManagementService
     */
    protected LeadManagementService $management;

    /**
     * Service responsible for querying and listing leads.
     *
     * @var LeadQueryService
     */
    protected LeadQueryService $query;

    /**
     * Inject the required services into the controller.
     *
     * @param  LeadLogService $logger Handles audit logging for lead events.
     * @param  LeadManagementService $management Handles lead
     * create/update/delete/restore.
     * @param  LeadQueryService $query Handles lead listing and retrieval.
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
     * Also includes the authenticated user's permissions for the Lead
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated lead data with pagination metadata and
     * permissions.
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
     * Validation is handled upstream by StoreLeadRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param  StoreLeadRequest $request Validated request containing lead data.
     *
     * @return JsonResponse The newly created lead, with HTTP 201 Created.
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
     * Returns a single lead by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  Lead $lead Route-model-bound lead instance.
     *
     * @return JsonResponse The resolved lead resource.
     */
    public function show(Lead $lead): JsonResponse
    {
        $this->authorize('view', $lead);
        $this->authorize('access', $lead);

        $lead = $this->query->show($lead);

        return response()->json($lead);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateLeadRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the
     * authenticated user.
     *
     * @param  UpdateLeadRequest $request Validated request containing updated
     * lead data.
     * @param  Lead $lead Route-model-bound lead instance to update.
     *
     * @return JsonResponse The updated lead resource.
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
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * lead instance is still fully accessible during logging.
     *
     * @param  Lead $lead Route-model-bound lead instance to delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
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
     * Restore the specified lead from soft deletion.
     *
     * Looks up the lead including trashed records, then authorises via
     * the 'restore' policy. Returns 404 if the lead is not currently
     * soft-deleted, preventing accidental double-restores.
     *
     * @param  int|string $id The primary key of the soft-deleted lead.
     *
     * @return JsonResponse The restored lead resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the lead is not trashed (404).
     */
    public function restore(int $id): JsonResponse
    {
        $lead = Lead::withTrashed()->findOrFail($id);
        $this->authorize('restore', $lead);

        if (! $lead->trashed()) {
            abort(404);
        }

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
