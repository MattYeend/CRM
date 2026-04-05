<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Services\Tasks\TaskLogService;
use App\Services\Tasks\TaskManagementService;
use App\Services\Tasks\TaskQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the Task resource.
 *
 * Delegates business logic to three dedicated services:
 *   - TaskLogService — records audit log entries for task changes
 *   - TaskManagementService — handles create, update, delete, and restore
 *      operations
 *   - TaskQueryService — handles read/list queries with filtering and
 *      pagination
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class TaskController extends Controller
{
    /**
     * Service responsible for writing audit log entries for task events.
     *
     * @var TaskLogService
     */
    protected TaskLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * tasks.
     *
     * @var TaskManagementService
     */
    protected TaskManagementService $management;

    /**
     * Service responsible for querying and listing tasks.
     *
     * @var TaskQueryService
     */
    protected TaskQueryService $query;

    /**
     * Inject the required services into the controller.
     *
     * @param  TaskLogService $logger Handles audit logging for
     * task events.
     * @param  TaskManagementService $management Handles task
     * create/update/delete/restore.
     * @param  TaskQueryService $query Handles task listing and
     * retrieval.
     */
    public function __construct(
        TaskLogService $logger,
        TaskManagementService $management,
        TaskQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
    }

    /**
     * Display a listing of the resource.
     *
     * Also includes the authenticated user's permissions for the Task
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated task data with pagination metadata and
     * permissions.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Task::class);

        $task = $this->query->list($request);

        return response()->json($task);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreTaskRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param  StoreTaskRequest $request Validated request containing
     * task data.
     *
     * @return JsonResponse The newly created task, with HTTP 201 Created.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->management->store($request);

        $user = $request->user();

        $this->logger->taskCreated(
            $user,
            $user->id,
            $task,
        );

        $task = $this->query->show($task);

        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     *
     * Returns a single task by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  Task $task Route-model-bound task instance.
     *
     * @return JsonResponse The resolved task resource.
     */
    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);
        $this->authorize('access', $task);

        $task = $this->query->show($task);

        return response()->json($task);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateTaskRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the
     * authenticated user.
     *
     * @param  UpdateTaskRequest $request Validated request containing
     * updated task data.
     * @param  Task $task Route-model-bound task instance to update.
     *
     * @return JsonResponse The updated task resource.
     */
    public function update(
        UpdateTaskRequest $request,
        Task $task
    ): JsonResponse {
        $task = $this->management->update($request, $task);

        $user = $request->user();

        $this->logger->taskUpdated(
            $user,
            $user->id,
            $task,
        );

        return response()->json($task);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * task instance is still fully accessible during logging.
     *
     * @param  Task $task Route-model-bound task instance to delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $user = auth()->user();

        $this->logger->taskDeleted(
            $user,
            $user->id,
            $task,
        );

        $this->management->destroy($task);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified task from soft deletion.
     *
     * Looks up the task including trashed records, then authorises via
     * the 'restore' policy. Returns 404 if the task is not currently
     * soft-deleted, preventing accidental double-restores.
     *
     * @param  int|string $id The primary key of the soft-deleted task.
     *
     * @return JsonResponse The restored task resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the task is not trashed (404).
     */
    public function restore(int $id): JsonResponse
    {
        $task = Task::withTrashed()->findOrFail($id);
        $this->authorize('restore', $task);

        if (! $task->trashed()) {
            abort(404);
        }

        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->taskRestored(
            $user,
            $user->id,
            $task
        );

        return response()->json($task);
    }
}
