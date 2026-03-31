<?php

namespace App\Services\Leads;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Handles search filtering for Lead queries.
 *
 * Currently supports filtering by owner_id.
 */
class LeadSearchService
{
    /**
     * Apply search filters to a Lead query.
     *
     * Checks request query parameters and applies filtering conditions
     * to the given Eloquent query builder.
     *
     * @param Builder $query   The Eloquent query builder instance for leads.
     * @param Request $request The HTTP request containing search parameters.
     *
     * @return void
     */
    public function applySearch(Builder $query, Request $request): void
    {
        $ownerId = $request->query('owner_id');

        if ($ownerId) {
            $query->where('owner_id', $ownerId);
        }
    }
}
