<?php

namespace App\Services;

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
        $data = $request->validated();

        $data['updated_by'] = $request->user()->id;

        $deal->update($data);

        return $deal->fresh();
    }
}
