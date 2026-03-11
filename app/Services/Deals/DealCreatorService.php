<?php

namespace App\Services\Deals;

use App\Http\Requests\StoreDealRequest;
use App\Models\Deal;

class DealCreatorService
{
    /**
     * Create a new deal from request data.
     *
     * @param StoreDealRequest $request
     *
     * @return Deal
     */
    public function create(StoreDealRequest $request): Deal
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;

        return Deal::create($data);
    }
}
