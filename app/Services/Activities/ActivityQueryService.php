<?php

namespace App\Services\Activities;

use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

class ActivityQueryService
{
    private ActivitySortingService $sorting;
    private ActivityTrashFilterService $trashFilter;
    public function __construct(
        ActivitySortingService $sorting,
        ActivityTrashFilterService $trashFilter,
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

        $query = Activity::with('user');

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $query->paginate($perPage)->appends($request->query());

        $paginator->through(fn (Activity $activity) => $this->formatActivity($activity));

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
     * @param Activity $activity
     *
     * @return array
     */
    public function show(Activity $activity): array
    {
        return $this->formatActivity($activity);
    }

    /**
     * Format a activity into an array with user_id, subject, and permissions.
     *
     * @param Activity $activity
     *
     * @return array
     */
    private function formatActivity(Activity $activity): array
    {
        $subjectName = null;

        if ($activity->subject) {
            $subjectName = $activity->subject->name ?? $activity->subject->title ?? null;
        }

        $activity->subject_name = $subjectName;

        return [
            'id' => $activity->id,
            'user_id' => $activity->user_id,
            'username' => $activity->user?->name,
            'type' => $activity->type,
            'subject_type' => $activity->subject_type,
            'description' => $activity->description,
            'subject_name' => $subjectName,
            'creator' => $activity->creator,
            'permissions' => [
                'view' => Gate::allows('view', $activity),
                'update' => Gate::allows('update', $activity),
                'delete' => Gate::allows('delete', $activity),
            ],
        ];
    }
}
