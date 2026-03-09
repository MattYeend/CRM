<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CompanySearchService
{
    /** apply search to the query.
     *
     * @param Builder $query
     *
     * @param Request $request
     *
     * @return void
     */
    public function applySearch($query, Request $request): void
    {
        $q = $request->query('q');

        if (! $q) {
            return;
        }

        $query->where('name', 'like', "%{$q}%");
    }
}
