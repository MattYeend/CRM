<?php

namespace App\Services\Tasks;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single task with related data loaded.
     *
     * @param  Task $task The route-model-bound task
     * instance.
     *
     * @return Task The task with relationships loaded.
     */
    public function show(Task $task): Task
    {
        return $task->load('assignee', 'creator', 'taskable');
    }
}
