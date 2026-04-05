<?php

namespace App\Services\Deals;

use App\Http\Requests\UpdateDealRequest;
use App\Models\Deal;

/**
 * Handles updates to Deal records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * updates to the deal.
 */
class DealUpdaterService
{
    /**
     * Update an existing deal.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user and timestamp to audit fields, updates the deal, and returns
     * a fresh instance.
     *
     * @param  UpdateDealRequest $request The request containing validated
     * deal data.
     * @param  Deal $deal The deal to update.
     *
     * @return Deal The updated deal instance.
     */
    public function update(
        UpdateDealRequest $request,
        Deal $deal
    ): Deal {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;

        $deal->update($data);

        return $deal->fresh();
    }
}
