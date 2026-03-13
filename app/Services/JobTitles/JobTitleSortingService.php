<?php

namespace App\Services\JobTitles;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class JobTitleSortingService
{
    /**
     * Constant for allowed sorts
     */
    private const ALLOWED_SORTS = [
        'id',
        'title',
        'short_code',
        'group',
        'created_at',
        'updated_at',
    ];

    /**
     * Apply sorting to the query.
     *
     * @param Builder $query
     *
     * @param Request $request
     *
     * @return void
     */
    public function applySorting($query, Request $request): void
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

