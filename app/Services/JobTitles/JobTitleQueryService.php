<?php

namespace App\Services\JobTitles;

use App\Models\JobTitle;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Handles read queries for JobTitle records.
 *
 * Delegates searching, sorting, and trash filtering to dedicated
 * sub-services and returns paginated or single invoice results with
 * the appropriate relationships loaded.
 */
class JobTitleQueryService
{
    /**
     * Service responsible for applying sort order.
     *
     * @var JobTitleSortingService
     */
    private JobTitleSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters.
     *
     * @var JobTitleTrashFilterService
     */
    private JobTitleTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  JobTitleSortingService $sorting Handles sort order.
     * @param  JobTitleTrashFilterService $trashFilter Handles
     * trash filtering.
     */
    public function __construct(
        JobTitleSortingService $sorting,
        JobTitleTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of job titles with search, sorting,
     * and trash filters applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry search,
     * sort, filter, and pagination params.
     *
     * @return LengthAwarePaginator Paginated job titles item results.
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
     * Return a single job title with related data loaded.
     *
     * @param  JobTitle $jobTitle The route-model-bound job title
     * instance.
     *
     * @return JobTitle The job title with relationships loaded.
     */
    public function show(JobTitle $jobTitle): JobTitle
    {
        return $jobTitle->load('users', 'creator', 'updater');
    }
}
