<?php

namespace App\Services\Pipelines;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PipelineTrashFilterService
{
    /**
     * Apply trash filters to the query.
     *
     * @param Builder $query
     *
     * @param Request $request
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
