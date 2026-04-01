<?php

namespace App\Services\Users;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Applies trash visibility filters to User queries.
 *
 * Supports filtering for only trashed records or including trashed
 * records alongside active ones.
 */
class UserTrashFilterService
{
    /**
     * Apply trash filters to the given query.
     *
     * If only_trashed is true, only soft-deleted records are returned.
     * If with_trashed is true, both active and soft-deleted records are
     * included. Otherwise, only active records are returned.
     *
     * @param  Builder $query The Eloquent query builder instance.
     * @param  Request $request Incoming HTTP request carrying trash
     * filter parameters.
     *
     * @return void
     */
    public function applyTrashFilters($query, Request $request): void
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
