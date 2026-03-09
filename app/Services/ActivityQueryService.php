<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityQueryService
{
    private ActivitySortingService $sorting;
    private TrashFilterService $trashFilter;
    public function __construct(
        ActivitySortingService $sorting,
        TrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated activities, applying filters/sorting.
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

        $query = Activity::query();

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single activity.
     *
     * @param Activity $activity
     *
     * @return Activity
     */
    public function show(Activity $activity): Activity
    {
        return $activity->load(['user', 'subject']);
    }
}
