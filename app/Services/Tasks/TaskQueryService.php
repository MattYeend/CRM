<?php

namespace App\Services\Tasks;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

/**
 * Handles read queries for Task records.
 *
 * Delegates searching, sorting, and trash filtering to dedicated
 * sub-services and returns paginated or single task results with
 * the appropriate relationships loaded.
 */
class TaskQueryService
{
    /**
     * Service responsible for applying sort order.
     *
     * @var TaskSortingService
     */
    private TaskSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters.
     *
     * @var TaskTrashFilterService
     */
    private TaskTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  TaskSortingService $sorting Handles sort order.
     * @param  TaskTrashFilterService $trashFilter Handles
     * trash filtering.
     */
    public function __construct(
        TaskSortingService $sorting,
        TaskTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of tasks with search, sorting,
     * and trash filters applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry search,
     * sort, filter, and pagination params.
     *
     * @return LengthAwarePaginator Paginated tasks item results.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = Task::with('assignee', 'creator', 'taskable');

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $query->paginate($perPage)->appends($request->query());

        $paginator->through(
            fn (Task $task) => $this->formatTask($task)
        );

        $paginator->appends([
            'permissions' => [
                'create' => Gate::allows('create', Task::class),
                'viewAny' => Gate::allows('viewAny', Task::class),
            ],
        ]);

        return $paginator;
    }

    /**
     * Return a single task with related data loaded.
     *
     * @param  Task $task The route-model-bound task
     * instance.
     *
     * @return array
     */
    public function show(Task $task): array
    {
        $task->load(
            'assignee',
            'creator',
            'taskable',
        );

        return $this->formatTask($task);
    }

    /**
     * Format a task into a structured array.
     *
     * Combines core attributes, assignment and polymorphic data,
     * derived lifecycle flags, metadata, and permissions.
     *
     * @param  Task $task
     *
     * @return array
     */
    private function formatTask(Task $task): array
    {
        return array_merge(
            $this->baseData($task),
            $this->assignmentData($task),
            $this->polymorphicData($task),
            $this->stateData($task),
            $this->relationshipData($task),
            $this->permissionData($task),
        );
    }

    /**
     * Extract core task attributes.
     *
     * @param  Task $task
     *
     * @return array
     */
    private function baseData(Task $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'priority' => $task->priority,
            'status' => $task->status,
            'due_at' => $task->due_at,
        ];
    }

    /**
     * Extract assignment-related data.
     *
     * @param  Task $task
     *
     * @return array
     */
    private function assignmentData(Task $task): array
    {
        return [
            'assigned_to' => $task->assigned_to,
            'assignee' => $task->assignee,
        ];
    }

    /**
     * Extract polymorphic taskable data.
     *
     * @param  Task $task
     *
     * @return array
     */
    private function polymorphicData(Task $task): array
    {
        return [
            'taskable_type' => $task->taskable_type,
            'taskable_id' => $task->taskable_id,
            'taskable_name' => $this->taskableName($task),
            'taskable' => $task->taskable,
        ];
    }

    /**
     * Extract derived lifecycle state flags.
     *
     * @param  Task $task
     *
     * @return array
     */
    private function stateData(Task $task): array
    {
        return [
            'is_overdue' => $task->getIsOverdueAttribute(),
            'is_pending' => $task->getIsPendingAttribute(),
            'is_completed' => $task->getIsCompletedAttribute(),
            'is_cancelled' => $task->getIsCancelledAttribute(),
        ];
    }

    /**
     * Extract related model data.
     *
     * @param  Task $task
     *
     * @return array
     */
    private function relationshipData(Task $task): array
    {
        return [
            'creator' => $task->creator,
        ];
    }

    /**
     * Determine authorisation permissions for the task.
     *
     * @param  Task $task
     *
     * @return array
     */
    private function permissionData(Task $task): array
    {
        return [
            'permissions' => [
                'view' => Gate::allows('view', $task),
                'update' => Gate::allows('update', $task),
                'delete' => Gate::allows('delete', $task),
            ],
        ];
    }

    /**
     * Resolve the taskable name for a task.
     *
     * Attempts to derive a displayable name from the related taskable
     * using common attributes such as `name` or `title`.
     *
     * @param  Task $task
     *
     * @return string|null
     */
    private function taskableName(Task $task): ?string
    {
        if ($task->taskable) {
            return $task->taskable->name
                ?? $task->taskable->title
                ?? null;
        }

        return null;
    }
}
