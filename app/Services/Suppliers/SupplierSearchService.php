<?php

namespace App\Services\Suppliers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Handles search filtering for Supplier queries.
 *
 * Currently supports filtering by name, code, and email.
 */
class SupplierSearchService
{
    /**
     * Apply search filters to a Supplier query.
     *
     * Checks request query parameters and applies filtering conditions
     * to the given Eloquent query builder.
     *
     * @param Builder $query   The Eloquent query builder instance for
     * suppliers.
     * @param Request $request The HTTP request containing search parameters.
     *
     * @return void
     */
    public function applySearch($query, Request $request): void
    {
        $search = $request->query('search');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
    }
}
