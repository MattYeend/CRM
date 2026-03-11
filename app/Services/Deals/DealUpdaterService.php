<?php

namespace App\Services\Deals;

use App\Http\Requests\UpdateDealRequest;
use App\Models\Deal;

class DealUpdaterService
{
    /**
     * Update the deal using request data.
     *
     * @param UpdateDealRequest $request
     *
     * @param Deal $deal
     *
     * @return Deal
     */
    public function update(
        UpdateDealRequest $request,
        Deal $deal
    ): Deal {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        $deal->update($data);

        return $deal->fresh();
    }
}
