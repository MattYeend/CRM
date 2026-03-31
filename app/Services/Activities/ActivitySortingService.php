<?php

namespace App\Services\Activities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Handles sorting logic for Activity model queries.
 *
 * This service applies validated sorting parameters to a query builder
 * instance, ensuring only allowed fields are used and enforcing a safe
 * default sort when invalid input is provided.
 */
class ActivitySortingService
{
    /**
     * Allowed sortable columns.
     */
    private const ALLOWED_SORTS = [
        'id',
        'user_id',
        'type',
        'subject_type',
        'description',
        'created_at',
        'updated_at',
    ];

    /**
     * Apply sorting to the query.
     *
     * Determines the sort column and direction from the request,
     * validates them against allowed values, and applies them
     * to the query builder.
     *
     * @param  Builder  $query    The query builder instance
     * @param  Request  $request  The incoming request containing sort
     * parameters
     *
     * @return void
     */
    public function applySorting(Builder $query, Request $request): void
    {
        $sortBy = $request->query('sort_by', 'id');

        if (! in_array($sortBy, self::ALLOWED_SORTS, true)) {
            $sortBy = 'id';
        }

        $sortDir = $request->query('sort_dir', 'desc') === 'asc'
            ? 'asc'
            : 'desc';

        $query->orderBy($sortBy, $sortDir);
    }
}
