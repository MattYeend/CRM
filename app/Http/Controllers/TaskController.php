<?php

namespace App\Http\Controllers;

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
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
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
        return response()->json($task->load('assignee', 'creator', 'taskable'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validateTaskData($request);
        $task = Task::create($data);

        $this->logger->taskCreated(
            auth()->user(),
            auth()->id(),
            $task,
        );

        $this->handlePolymorphicAssignment($task, $data);

        return response()->json(
            $task->load('assignee', 'creator', 'taskable'),
            201
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @param \App\Models\Task $task
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|integer|exists:users,id',
            'created_by' => 'nullable|integer|exists:users,id',
            'priority' => 'nullable|in:low,medium,high',
            'status' => 'nullable|in:pending,completed,canceled',
            'due_at' => 'nullable|date',
        ]);

        $task->update($data);

        $this->logger->taskUpdated(
            auth()->user(),
            auth()->id(),
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
     * @param \App\Models\Task $task
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->logger->taskDeleted(
            auth()->user(),
            auth()->id(),
            $task,
        );

        $task->delete();

        return response()->json(null, 204);
    }

    /**
     * Validate task data from the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    private function validateTaskData(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|integer|exists:users,id',
            'created_by' => 'nullable|integer|exists:users,id',
            'taskable_type' => 'nullable|string',
            'taskable_id' => 'nullable|integer',
            'priority' => 'nullable|in:low,medium,high',
            'status' => 'nullable|in:pending,completed,canceled',
            'due_at' => 'nullable|date',
        ]);
    }

    /**
     * Handle polymorphic assignment of the task to another model.
     *
     * @param \App\Models\Task $task
     *
     * @param array $data
     *
     * @return void
     */
    private function handlePolymorphicAssignment(Task $task, array $data): void
    {
        if (! isset($data['taskable_type'], $data['taskable_id'])) {
            return;
        }

        try {
            $model = app($data['taskable_type'])->find($data['taskable_id']);
            if ($model) {
                $model->tasks()->save($task);
            }
        } catch (\Throwable $e) {
            report($e); // use Laravel's error reporting instead of echo
        }
    }
}
