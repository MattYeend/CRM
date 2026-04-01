<?php

namespace App\Services\PartSerialNumbers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Applies search filters to PartSerialNumber queries.
 *
 * Filters records by name, code, or email when a search term is present
 * in the request.
 */
class PartSerialNumberSearchService
{
    /**
     * Apply search filtering to the given query.
     *
     * When a search term is present, filters records where name, code, or
     * email contain the term as a substring. Returns the query unchanged if
     * no search term is provided.
     *
     * @param  Builder $query The Eloquent query builder instance to filter.
     * @param  Request $request Incoming HTTP request carrying an optional
     * search parameter.
     *
     * @return Builder The modified query builder instance.
     */
    public function applySearch(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->query('search'), function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
    }
}
