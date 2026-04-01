<?php

namespace App\Services\PartImages;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Applies trash visibility filters to PartImage queries.
 *
 * Supports three visibility modes driven by request boolean flags:
 * only trashed records, trashed records included alongside active ones,
 * or the default behaviour of active records only.
 */
class PartImageTrashFilterService
{
    /**
     * Apply trash visibility filters to the given query.
     *
     * When only_trashed is true, the query is scoped to soft-deleted records
     * only. When with_trashed is true, soft-deleted records are included
     * alongside active ones. If neither flag is set the query returns active
     * records only.
     *
     * @param  Builder $query The Eloquent query builder instance to filter.
     * @param  Request $request Incoming HTTP request carrying only_trashed
     * and with_trashed boolean flags.
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
