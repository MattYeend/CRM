<?php

namespace App\Services\Companies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Applies trash visibility filters to Company queries.
 *
 * Supports filtering for only trashed records or including trashed records
 * alongside active ones, based on request parameters.
 */
class CompanyTrashFilterService
{
    /**
     * Apply trash filters to the given query based on the request parameters.
     *
     * If only_trashed is true, only soft-deleted records are returned.
     * If with_trashed is true, both active and soft-deleted records
     * are included.
     * Defaults to returning only non-deleted records.
     *
     * @param  Builder $query The Eloquent query builder instance to filter.
     * @param  Request $request Incoming HTTP request carrying trash filter
     * flags.
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
