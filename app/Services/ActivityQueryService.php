<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityQueryService
{
    /**
     * Return paginated users with roles, applying filters/sorting.
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

        $this->applyTrashFilters($query, $request);
        $this->applySorting($query, $request);

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

    /**
     * Apply trash filters to the query.
     *
     * @param Builder $query
     *
     * @param Request $request
     *
     * @return void
     */
    private function applyTrashFilters($query, Request $request): void
    {
        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();

            return;
        }

        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }
    }

    /**
     * Apply sorting to the query.
     *
     * @param Builder $query
     *
     * @param Request $request
     *
     * @return void
     */
    private function applySorting($query, Request $request): void
    {
        $allowedSorts = [
            'id',
            'type',
            'subject_type',
            'created_at',
            'updated_at',
        ];

        $sortBy = $request->query('sort_by', 'id');

        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id';
        }

        $sortDir = $request->query('sort_dir', 'desc') === 'asc'
            ? 'asc'
            : 'desc';

        $query->orderBy($sortBy, $sortDir);
    }
}
