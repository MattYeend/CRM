<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LeadSeachService
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
        $ownerId = $request->query('owner_id');

        if ($ownerId) {
            $query->where('owner_id', $ownerId);
        }
    }
}
