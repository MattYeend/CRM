<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Services\Tasks\TaskLogService;
use App\Services\Tasks\TaskManagementService;
use App\Services\Tasks\TaskQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Declare a protected property to hold the TaskLogService,
     * TaskManagementService and TaskQueryService instance
     *
     * @var TaskLogService
     * @var TaskManagementService
     * @var TaskQueryService
     */
    protected TaskLogService $logger;
    protected TaskManagementService $management;
    protected TaskQueryService $query;

    /**
     * Constructor for the controller
     *
     * @param TaskLogService $logger
     *
     * @param TaskManagementService $management
     *
     * @param TaskQueryService $query
     *
     * An instance of the TaskLogService used for logging
     * task-related actions
     * An instance of the TaskManagementService for management
     * of tasks
     * An instance of the TaskQueryService for the query of
     * task-related actions
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
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Task::class);

        $task = $this->query->list($request);

        return response()->json($task);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Task $task
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $task = $this->query->show($task);

        return response()->json($task->load('assignee', 'creator', 'taskable'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreTaskRequest $request
     *
     * @return JsonResponse
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

        return response()->json(
            $task->load('assignee', 'creator', 'taskable'),
            201
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTaskRequest $request
     *
     * @param Task $task
     *
     * @return JsonResponse
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

        return response()->json($task->fresh()->load(
            'assignee',
            'creator',
            'taskable',
        ));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Task $task
     *
     * @return JsonResponse
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
     * Restore the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $task = Task::withTrashed()->findOrFail($id);
        $this->authorize('restore', $task);
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
