<?php

namespace App\Services\PipelineStages;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Applies sort order to PipelineStage queries.
 *
 * Validates the requested sort column against an allowlist and falls back
 * to sorting by id descending when an invalid column is supplied.
 */
class PipelineStageSortingService
{
    /**
     * The columns that may be used as sort targets.
     */
    private const ALLOWED_SORTS = [
        'id',
        'pipeline_id',
        'name',
        'position',
        'is_won_stage',
        'is_lost_stage',
        'created_at',
        'updated_at',
    ];

    /**
     * Apply sorting to the given query based on the request parameters.
     *
     * Validates sort_by against the allowlist, defaulting to 'id' if the
     * value is not permitted. Accepts 'asc' or 'desc' for sort_dir,
     * defaulting to 'desc'.
     *
     * @param  Builder $query The Eloquent query builder instance to sort.
     * @param  Request $request Incoming HTTP request carrying sort_by and
     * sort_dir parameters.
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
