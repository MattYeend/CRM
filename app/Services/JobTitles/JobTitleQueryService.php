<?php

namespace App\Services\JobTitles;

use App\Models\JobTitle;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class JobTitleQueryService
{
    private JobTitleSortingService $sorting;
    private JobTitleTrashFilterService $trashFilter;
    public function __construct(
        JobTitleSortingService $sorting,
        JobTitleTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated job titles, applying filters/sorting.
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

        $query = JobTitle::with('users', 'creator', 'updater');

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single job title
     *
     * @param JobTitle $jobTitle
     *
     * @return JobTitle
     */
    public function show(JobTitle $jobTitle): JobTitle
    {
        return $jobTitle->load('users', 'creator', 'updater');
    }
}
