<?php

namespace App\Services\Activities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Handles soft delete filtering for Activity model queries.
 *
 * This service applies request-driven filters to include only trashed
 * models, include both trashed and non-trashed models, or default to
 * excluding trashed models.
 */
class ActivityTrashFilterService
{
    /**
     * Apply trash filters to the query.
     *
     * Evaluates request parameters to determine whether to restrict
     * results to only trashed models or include trashed models alongside
     * active ones.
     *
     * @param  Builder $query The query builder instance
     * @param  Request $request The incoming request containing filter
     * parameters
     *
     * @return void
     */
    public function applyTrashFilters(Builder $query, Request $request): void
    {
        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();

            return;
        }

        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }
    }
}
