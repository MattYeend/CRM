<?php

namespace App\Services\Deals;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Applies search filters to Deal queries.
 *
 * Supports filtering by common deal attributes such as status
 * and owner.
 */
class DealSearchService
{
    /**
     * Apply search filtering to the given query.
     *
     * Filters deals by status and owner_id when provided in the request.
     * If no filters are supplied, the query is left unchanged.
     *
     * @param  Builder $query The Eloquent query builder instance to filter.
     * @param  Request $request Incoming HTTP request carrying search
     * parameters.
     *
     * @return void
     */
    public function applySearch(Builder $query, Request $request): void
    {
        $status = $request->query('status');
        $ownerId = $request->query('owner_id');

        if ($status) {
            $query->where('status', $status);
        }

        if ($ownerId) {
            $query->where('owner_id', $ownerId);
        }
    }
}
