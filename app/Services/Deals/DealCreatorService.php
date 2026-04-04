<?php

namespace App\Services\Deals;

use App\Http\Requests\StoreDealRequest;
use App\Models\Deal;

/**
 * Handles creation of Deal records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * a new deal record.
 */
class DealCreatorService
{
    /**
     * Create a new deal.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user and timestamp to audit fields, and creates the deal record.
     *
     * @param  StoreDealRequest $request The request containing validated
     * deal data.
     *
     * @return Deal The newly created deal instance.
     */
    public function create(StoreDealRequest $request): Deal
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;

        return Deal::create($data);
    }
}
