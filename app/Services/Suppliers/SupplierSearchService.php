<?php

namespace App\Services\Suppliers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SupplierSearchService
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
        $search = $request->query('search');

        if($search){
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
    }
}
