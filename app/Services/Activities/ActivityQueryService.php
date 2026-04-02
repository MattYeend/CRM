<?php

namespace App\Services\Activities;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

/**
 * Handles querying and presentation of Activity models.
 *
 * This service is responsible for retrieving Activity models with support
 * for filtering, sorting, pagination, and transformation into a consistent
 * response structure. It also enriches results with related data and
 * authorisation metadata for frontend consumption.
 */
class ActivityQueryService
{
    private ActivitySortingService $sorting;
    private ActivityTrashFilterService $trashFilter;

    /**
     * Create a new service instance.
     *
     * @param  ActivitySortingService      $sorting
     * @param  ActivityTrashFilterService  $trashFilter
     *
     * @return void
     */
    public function __construct(
        ActivitySortingService $sorting,
        ActivityTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated activities, applying filters and sorting.
     *
     * Applies query parameters, eager loads relationships, and appends
     * permission metadata to the paginated result set.
     *
     * @param  Request  $request  The incoming request containing query
     * parameters
     *
     * @return LengthAwarePaginator
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = Activity::with('user');

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $query->paginate($perPage)->appends($request->query());

        $paginator->through(
            fn (Activity $activity) => $this->formatActivity($activity)
        );

        $paginator->appends([
            'permissions' => [
                'create' => Gate::allows('create', Activity::class),
                'viewAny' => Gate::allows('viewAny', Activity::class),
            ],
        ]);

        return $paginator;
    }

    /**
     * Return a single activity.
     *
     * Formats the activity into a consistent response structure.
     *
     * @param  Activity  $activity  The activity model to retrieve
     *
     * @return array
     */
    public function show(Activity $activity): array
    {
        return $this->formatActivity($activity);
    }

    /**
     * Format a activity into a structured array.
     *
     * Includes core attributes, related user data, derived subject name,
     * and authorisation permissions for the current user.
     *
     * @param  Activity  $activity
     *
     * @return array
     */
    private function formatActivity(Activity $activity): array
    {
        return [
            'id' => $activity->id,
            'assigned_to' => $activity->assigned_to,
            'username' => $activity->user?->name,
            'type' => $activity->type,
            'subject_type' => $activity->subject_type,
            'description' => $activity->description,
            'subject_name' => $this->subjectName($activity),
            'creator' => $activity->creator,
            'permissions' => [
                'view' => Gate::allows('view', $activity),
                'update' => Gate::allows('update', $activity),
                'delete' => Gate::allows('delete', $activity),
            ],
        ];
    }

    /**
     * Resolve the subject name for an activity.
     *
     * Attempts to derive a displayable name from the related subject
     * using common attributes such as `name` or `title`.
     *
     * @param  Activity  $activity
     *
     * @return string
     */
    private function subjectName(Activity $activity): string
    {
        if ($activity->subject) {
            $subjectName = $activity->subject->name
                ?? $activity->subject->title
                ?? null;
        }

        return $activity->subject_name = $subjectName;
    }
}
