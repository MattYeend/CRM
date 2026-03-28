<?php

namespace App\Services\PartSerialNumbers;

use App\Http\Requests\StorePartSerialNumberRequest;
use App\Models\PartSerialNumber;

class PartSerialNumberCreatorService
{
    /**
     * Create a new part serial number from request data.
     *
     * @param StorePartSerialNumberRequest $request
     *
     * @return PartSerialNumber
     */
    public function create(
        StorePartSerialNumberRequest $request
    ): PartSerialNumber {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;
        $data['created_at'] = now();

        return PartSerialNumber::create($data);
    }
}
