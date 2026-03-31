<?php

namespace App\Services\Companies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Applies search filters to Company queries.
 *
 * Supports simple text-based searching across company attributes.
 */
class CompanySearchService
{
    /**
     * Apply search filtering to the given query.
     *
     * If a search query is provided, filters companies by name using
     * a partial match. If no query is provided, the query is left unchanged.
     *
     * @param  Builder $query The Eloquent query builder instance to filter.
     * @param  Request $request Incoming HTTP request carrying search
     * parameters.
     *
     * @return void
     */
    public function applySearch(Builder $query, Request $request): void
    {
        $q = $request->query('q');

        if (! $q) {
            return;
        }

        $query->where('name', 'like', "%{$q}%");
    }
}
