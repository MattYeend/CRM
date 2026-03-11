<?php

namespace App\Services\Contacts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ContactSearchService
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
        $companyId = $request->query('company_id');
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        $q = $request->query('q');

        if (! $q) {
            return;
        }

        $query->where(function ($subQuery) use ($q) {
            $subQuery->where('first_name', 'like', '%' . $q . '%')
                ->orWhere('last_name', 'like', '%' . $q . '%')
                ->orWhere('email', 'like', '%' . $q . '%');
        });
    }
}
