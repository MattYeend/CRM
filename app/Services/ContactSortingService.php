<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ContactSortingService
{
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
        $allowedSorts = [
            'id',
            'first_name',
            'last_name',
            'email',
            'created_at',
            'updated_at',
        ];

        $sortBy = $request->query('sort_by', 'id');

        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id';
        }

        $sortDir = $request->query('sort_dir', 'desc') === 'asc'
            ? 'asc'
            : 'desc';

        $query->orderBy($sortBy, $sortDir);
    }
}
