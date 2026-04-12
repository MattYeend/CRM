<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;
use App\Services\Activities\ActivityLogService;
use App\Services\Activities\ActivityManagementService;
use App\Services\Activities\ActivityQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the Activity resource.
 *
 * Delegates business logic to three dedicated services:
 *   - ActivityLogService — records audit log entries for activity changes
 *   - ActivityManagementService — handles create, update, delete, and restore
 *      operations
 *   - ActivityQueryService — handles read/list queries with filtering and
 *      pagination
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class ActivityController extends Controller
{
    /**
     * Service responsible for writing audit log entries for activity events.
     *
     * @var ActivityLogService
     */
    protected ActivityLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * activities.
     *
     * @var ActivityManagementService
     */
    protected ActivityManagementService $management;

    /**
     * Service responsible for querying and listing activities.
     *
     * @var ActivityQueryService
     */
    protected ActivityQueryService $query;

    /**
     * Inject the required services into the controller.
     *
     * @param  ActivityLogService $logger Handles audit logging for activity
     * events.
     * @param  ActivityManagementService $management Handles activity
     * create/update/delete/restore.
     * @param  ActivityQueryService $query Handles activity listing and
     * retrieval.
     */
    public function __construct(
        ActivityLogService $logger,
        ActivityManagementService $management,
        ActivityQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
    }

    /**
     * Display a paginated listing of the resource.
     *
     * Also includes the authenticated user's permissions for the Activity
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated activity data with pagination metadata and
     * permissions.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Activity::class);

        $activities = $this->query->list($request);

        return response()->json($activities);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreActivityRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param  StoreActivityRequest $request Validated request containing
     * activity data.
     *
     * @return JsonResponse The newly created activity, with HTTP 201 Created.
     */
    public function store(StoreActivityRequest $request): JsonResponse
    {
        $activity = $this->management->store($request);
        $user = $request->user();

        $this->logger->activityCreated(
            $user,
            $user->id,
            $activity,
        );

        return response()->json($activity, 201);
    }

    /**
     * Display the specified resource.
     *
     * Return a single activity by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  Activity $activity Route-model-bound activity instance.
     *
     * @return JsonResponse The resolved activity resource.
     */
    public function show(Activity $activity): JsonResponse
    {
        $this->authorize('view', $activity);
        $this->authorize('access', $activity);

        $activity = $this->query->show($activity);

        return response()->json($activity);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateActivityRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the
     * authenticated user.
     *
     * @param  UpdateActivityRequest $request Validated request containing
     * updated activity data.
     * @param  Activity $activity Route-model-bound activity instance to update.
     *
     * @return JsonResponse The updated activity resource.
     */
    public function update(
        UpdateActivityRequest $request,
        Activity $activity
    ): JsonResponse {
        $activity = $this->management->update($request, $activity);

        $user = $request->user();

        $this->logger->activityUpdated(
            $user,
            $user->id,
            $activity,
        );

        return response()->json($activity);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * activity instance is still fully accessible during logging.
     *
     * @param  Activity $activity Route-model-bound activity instance to delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
     */
    public function destroy(Activity $activity): JsonResponse
    {
        $this->authorize('delete', $activity);

        $user = auth()->user();

        $this->logger->activityDeleted(
            $user,
            $user->id,
            $activity,
        );

        $this->management->destroy($activity);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified user from soft deletion.
     *
     * Looks up the activity including trashed records, then authorises via
     * the 'restore' policy. Returns 404 if the activity is not currently
     * soft-deleted, preventing accidental double-restores.
     *
     * @param  int|string $id The primary key of the soft-deleted activity.
     *
     * @return JsonResponse The restored activity resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the activity is not trashed (404).
     */
    public function restore($id): JsonResponse
    {
        $activity = Activity::withTrashed()->findOrFail($id);
        $this->authorize('restore', $activity);

        if (! $activity->trashed()) {
            abort(404);
        }

        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->activityRestored(
            $user,
            $user->id,
            $activity,
        );

        return response()->json($activity);
    }
}
