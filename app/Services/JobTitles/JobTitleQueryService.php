<?php

namespace App\Services\JobTitles;

use App\Models\JobTitle;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

/**
 * Handles read queries for JobTitle records.
 *
 * Delegates searching, sorting, and trash filtering to dedicated
 * sub-services and returns paginated or single job title results with
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

        $paginator = $query->paginate($perPage)->appends($request->query());

        $paginator->through(
            fn (JobTitle $jobTitle) => $this->formatJobTitle($jobTitle)
        );

        $paginator->appends([
            'permissions' => [
                'create' => Gate::allows('create', JobTitle::class),
                'viewAny' => Gate::allows('viewAny', JobTitle::class),
            ],
        ]);

        return $paginator;
    }

    /**
     * Return a single job title with related data loaded.
     *
     * @param  JobTitle $jobTitle The route-model-bound job title
     * instance.
     *
     * @return array
     */
    public function show(JobTitle $jobTitle): array
    {
        $jobTitle->load(
            'users',
            'creator',
            'updater',
        );

        return $this->formatJobTitle($jobTitle);
    }

    /**
     * Format a job title into a structured array.
     *
     * Includes core attributes, related data, derived accessors,
     * and authorisation permissions for the current user.
     *
     * @param  JobTitle $jobTitle
     *
     * @return array
     */
    private function formatJobTitle(JobTitle $jobTitle): array
    {
        return [
            'id' => $jobTitle->id,
            'title' => $jobTitle->title,
            'short_code' => $jobTitle->short_code,
            'group' => $jobTitle->group,
            'is_csuite' => $jobTitle->is_csuite,
            'is_executive' => $jobTitle->is_executive,
            'is_director' => $jobTitle->is_director,
            'user_count' => $jobTitle->user_count,
            'users' => $jobTitle->users,
            'creator' => $jobTitle->creator,
            'permissions' => [
                'view' => Gate::allows('view', $jobTitle),
                'update' => Gate::allows('update', $jobTitle),
                'delete' => Gate::allows('delete', $jobTitle),
            ],
        ];
    }
}
