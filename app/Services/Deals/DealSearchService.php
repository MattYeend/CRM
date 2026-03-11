<?php

namespace App\Services\Deals;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DealSearchService
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
        $status = $request->query('status');
        $ownerId = $request->query('owner_id');
        if ($status) {
            $query->where('status', $status);
        }

        if ($ownerId) {
            $query->where('owner_id', $ownerId);
        }
    }
}
