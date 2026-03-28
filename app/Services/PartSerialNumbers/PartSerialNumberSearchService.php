<?php

namespace App\Services\PartSerialNumbers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PartSerialNumberSearchService
{
    /**
     * Apply search to the query.
     *
     * @param Builder $query
     * @param Request $request
     *
     * @return Builder
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
