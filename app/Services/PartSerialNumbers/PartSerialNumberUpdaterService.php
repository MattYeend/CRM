<?php

namespace App\Services\PartSerialNumbers;

use App\Http\Requests\UpdatePartSerialNumberRequest;
use App\Models\PartSerialNumber;

class PartSerialNumberUpdaterService
{
    /**
     * Update the part serial number using request data.
     *
     * @param UpdatePartSerialNumberRequest $request
     *
     * @param PartSerialNumber $partSerialNumber
     *
     * @return PartSerialNumber
     */
    public function update(
        UpdatePartSerialNumberRequest $request,
        PartSerialNumber $partSerialNumber
    ): PartSerialNumber {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        $partSerialNumber->update($data);

        return $partSerialNumber->fresh();
    }
}
