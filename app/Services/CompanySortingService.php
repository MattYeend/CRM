<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CompanySortingService
{
    /**
     * Constants for allowed sorts
     */
    private const ALLOWED_SORTS = [
        'id',
        'name',
        'industry',
        'website',
        'phone',
        'address',
        'city',
        'region',
        'post_code',
        'country',
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
