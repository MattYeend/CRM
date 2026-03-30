<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLearningRequest;
use App\Http\Requests\UpdateLearningRequest;
use App\Models\Learning;
use App\Services\Learnings\LearningLogService;
use App\Services\Learnings\LearningManagementService;
use App\Services\Learnings\LearningQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the Learning resource.
 *
 * Delegates business logic to three dedicated services:
 *   - LearningLogService — records audit log entries for learning changes
 *   - LearningManagementService — handles create, update, delete, restore,
 *      and completion operations
 *   - LearningQueryService — handles read/list queries with filtering and
 *      pagination
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */

class LearningController extends Controller
{
    /**
     * Service responsible for writing audit log entries for learning events.
     *
     * @var LearningLogService
     */
    protected LearningLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, restoring, and
     * managing completion state of learnings.
     *
     * @var LearningManagementService
     */
    protected LearningManagementService $management;

    /**
     * Service responsible for querying and listing learnings.
     *
     * @var LearningQueryService
     */
    protected LearningQueryService $query;

    /**
     * Inject the required services into the controller.
     *
     * @param  LearningLogService $logger Handles audit logging for learning
     * events.
     *
     * @param  LearningManagementService $management Handles learning
     * create/update/delete/restore and completion transitions.
     *
     * @param  LearningQueryService $query Handles learning listing and
     * retrieval.
     */
    public function __construct(
        LearningLogService $logger,
        LearningManagementService $management,
        LearningQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
    }

    /**
     * Display a listing of the resource.
     *
     * Also includes the authenticated user's permissions for the Learning
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated learning data.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Learning::class);

        $learning = $this->query->list($request);

        return response()->json($learning);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreLearningRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param  StoreLearningRequest $request Validated request containing
     * learning data.
     *
     * @return JsonResponse The newly created learning, with HTTP 201 Created.
     */
    public function store(StoreLearningRequest $request): JsonResponse
    {
        $learning = $this->management->store($request);

        $user = $request->user();

        $this->logger->learningCreated(
            $user,
            $user->id,
            $learning,
        );

        return response()->json($learning, 201);
    }

    /**
     * Display the specified resource.
     *
     * Returns a single learning by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  Learning $learning Route-model-bound learning instance.
     *
     * @return JsonResponse The resolved learning resource.
     */
    public function show(Learning $learning): JsonResponse
    {
        $this->authorize('view', $learning);

        $learning = $this->query->show($learning);

        return response()->json($learning);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateLearningRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the
     * authenticated user.
     *
     * @param  UpdateLearningRequest $request Validated request containing
     * updated learning data.
     *
     * @param  Learning $learning Route-model-bound learning instance to update.
     *
     * @return JsonResponse The updated learning resource.
     */
    public function update(
        UpdateLearningRequest $request,
        Learning $learning
    ): JsonResponse {
        $learning = $this->management->update($request, $learning);

        $user = $request->user();

        $this->logger->learningUpdated(
            $user,
            $user->id,
            $learning,
        );

        return response()->json($learning);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * learning instance is still fully accessible during logging.
     *
     * @param  Learning $learning Route-model-bound learning instance to delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
     */
    public function destroy(Learning $learning): JsonResponse
    {
        $this->authorize('delete', $learning);

        $user = auth()->user();

        $this->logger->learningDeleted(
            $user,
            $user->id,
            $learning,
        );

        $this->management->destroy($learning);

        return response()->json(null, 204);
    }

    /**
     * Mark the specified resource as completed.
     *
     * Authorises via the 'complete' policy before proceeding.
     *
     * After transitioning the learning to a completed state, an audit log
     * entry is written against the authenticated user.
     *
     * @param  Request $request Incoming HTTP request.
     *
     * @param  Learning $learning Route-model-bound learning instance to
     * complete.
     *
     * @return JsonResponse The updated learning resource reflecting the
     * completed state.
     */
    public function complete(Request $request, Learning $learning): JsonResponse
    {
        $this->authorize('complete', $learning);

        $user = $request->user();

        $this->logger->learningComplete(
            $user,
            $user->id,
            $learning,
        );

        $learning = $this->management->complete($learning);

        return response()->json($learning);
    }

    /**
     * Mark the specified resource as incomplete.
     *
     * Authorises via the 'incomplete' policy before proceeding.
     *
     * After reverting the learning to an incomplete state, an audit log
     * entry is written against the authenticated user.
     *
     * @param  Request $request Incoming HTTP request.
     *
     * @param  Learning $learning Route-model-bound learning instance to mark
     * incomplete.
     *
     * @return JsonResponse The updated learning resource reflecting the
     * incomplete state.
     */
    public function incomplete(
        Request $request,
        Learning $learning
    ): JsonResponse {
        $this->authorize('incomplete', $learning);

        $user = $request->user();

        $this->logger->learningIncomplete(
            $user,
            $user->id,
            $learning,
        );

        $learning = $this->management->incomplete($learning);

        return response()->json($learning);
    }

    /**
     * Restore the specified learning from soft deletion.
     *
     * Looks up the learning including trashed records, then authorises via
     * the 'restore' policy. Returns 404 if the learning is not currently
     * soft-deleted, preventing accidental double-restores.
     *
     * @param  int|string $id The primary key of the soft-deleted learning.
     *
     * @return JsonResponse The restored learning resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the learning is not trashed (404).
     */
    public function restore(int $id): JsonResponse
    {
        $learning = Learning::withTrashed()->findOrFail($id);
        $this->authorize('restore', $learning);

        if (! $learning->trashed()) {
            abort(404);
        }

        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->learningRestored(
            $user,
            $user->id,
            $learning
        );

        return response()->json($learning);
    }
}
