<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Services\TaskLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Declare a protected property to hold the TaskLogService instance
     *
     * @var TaskLogService
     */
    protected TaskLogService $logger;

    /**
     * Constructor for the controller
     *
     * @param TaskLogService $logger
     *
     * An instance of the TaskLogService used for logging
     * task-related actions
     */
    public function __construct(TaskLogService $logger)
    {
        $this->logger = $logger;
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

        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        return response()->json(
            Task::with('assignee', 'creator', 'taskable')->paginate($perPage)
        );
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
        $user = $request->user();
        $data = $request->validated();
        $data['created_by'] = $user->id;

        $task = Task::create($data);

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
        $user = $request->user();
        $data = $request->validated();
        $data['updated_by'] = $user->id;

        $task->update($data);

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

        $task->update([
            'deleted_by' => $user->id,
        ]);

        $task->delete();

        return response()->json(null, 204);
    }
}
