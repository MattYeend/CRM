<?php

namespace App\Services\Tasks;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskQueryService
{
    private TaskSortingService $sorting;
    private TaskTrashFilterService $trashFilter;
    public function __construct(
        TaskSortingService $sorting,
        TaskTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated task, applying filters/sorting.
     *
     * @param Request $request
     *
     * @return LengthAwarePaginator
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
     * Return a single task.
     *
     * @param Task $task
     *
     * @return Task
     */
    public function show(Task $task): Task
    {
        return $task->load('assignee', 'creator', 'taskable');
    }
}
