<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobTitleRequest;
use App\Http\Requests\UpdateJobTitleRequest;
use App\Models\JobTitle;
use App\Services\JobTitles\JobTitleLogService;
use App\Services\JobTitles\JobTitleManagementService;
use App\Services\JobTitles\JobTitleQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the JobTitle resource.
 *
 * Delegates business logic to three dedicated services:
 *   - JobTitleLogService — records audit log entries for job title changes
 *   - JobTitleManagementService — handles create, update, delete, and restore
 *      operations
 *   - JobTitleQueryService — handles read/list queries with filtering and
 *      pagination
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class JobTitleController extends Controller
{
    /**
     * Service responsible for writing audit log entries for job title events.
     *
     * @var JobTitleLogService
     */
    protected JobTitleLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * job titles.
     *
     * @var JobTitleManagementService
     */
    protected JobTitleManagementService $management;

    /**
     * Service responsible for querying and listing job titles.
     *
     * @var JobTitleQueryService
     */
    protected JobTitleQueryService $query;

    /**
     * Inject the required services into the controller.
     *
     * @param  JobTitleLogService $logger Handles audit logging for job title
     * events.
     * @param  JobTitleManagementService $management Handles job title
     * create/update/delete/restore.
     * @param  JobTitleQueryService $query Handles job title listing and
     * retrieval.
     */
    public function __construct(
        JobTitleLogService $logger,
        JobTitleManagementService $management,
        JobTitleQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
    }

    /**
     * Display a listing of the resource.
     *
     * Also includes the authenticated user's permissions for the Job Title
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated job title data with pagination
     * metadata and permissions.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', JobTitle::class);

        $jobTitles = $this->query->list($request);

        return response()->json($jobTitles);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreJobTitleRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param  StoreJobTitleRequest $request Validated request containing
     * job title data.
     *
     * @return JsonResponse The newly created job title, with HTTP 201 Created.
     */
    public function store(StoreJobTitleRequest $request): JsonResponse
    {
        $jobTitle = $this->management->store($request);

        $user = $request->user();

        $this->logger->jobTitleCreated(
            $user,
            $user->id,
            $jobTitle,
        );

        return response()->json($jobTitle, 201);
    }

    /**
     * Display the specified resource.
     *
     * Returns a single job title by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  JobTitle $jobTitle Route-model-bound job title instance.
     *
     * @return JsonResponse The resolved job title resource.
     */
    public function show(JobTitle $jobTitle): JsonResponse
    {
        $this->authorize('view', $jobTitle);
        $this->authorize('access', $jobTitle);

        $jobTitle = $this->query->show($jobTitle);

        return response()->json($jobTitle);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateJobTitleRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the
     * authenticated user.
     *
     * @param  UpdateJobTitleRequest $request Validated request containing
     * updated job title data.
     * @param  JobTitle $jobTitle Route-model-bound job title instance to
     * update.
     *
     * @return JsonResponse The updated job title resource.
     */
    public function update(
        UpdateJobTitleRequest $request,
        JobTitle $jobTitle
    ): JsonResponse {
        $jobTitle = $this->management->update($request, $jobTitle);

        $user = $request->user();

        $this->logger->jobTitleUpdated(
            $user,
            $user->id,
            $jobTitle,
        );

        return response()->json($jobTitle);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * job title instance is still fully accessible during logging.
     *
     * @param  JobTitle $jobTitle Route-model-bound job title instance to
     * delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
     */
    public function destroy(JobTitle $jobTitle): JsonResponse
    {
        $this->authorize('delete', $jobTitle);

        $user = auth()->user();

        $this->logger->jobTitleDeleted(
            $user,
            $user->id,
            $jobTitle,
        );

        $this->management->destroy($jobTitle);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified job title from soft deletion.
     *
     * Looks up the job title including trashed records, then authorises via
     * the 'restore' policy. Returns 404 if the job title is not currently
     * soft-deleted, preventing accidental double-restores.
     *
     * @param  int|string $id The primary key of the soft-deleted job title.
     *
     * @return JsonResponse The restored job title resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the job title is not trashed (404).
     */
    public function restore(int $id): JsonResponse
    {
        $jobTitle = JobTitle::withTrashed()->findOrFail($id);
        $this->authorize('restore', $jobTitle);

        if (! $jobTitle->trashed()) {
            abort(404);
        }

        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->jobTitleRestored(
            $user,
            $user->id,
            $jobTitle
        );

        return response()->json($jobTitle);
    }
}
