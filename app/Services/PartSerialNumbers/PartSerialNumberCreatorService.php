<?php

namespace App\Services\PartSerialNumbers;

use App\Http\Requests\StorePartSerialNumberRequest;
use App\Models\Part;
use App\Models\PartSerialNumber;

class PartSerialNumberCreatorService
{
    /**
     * Create a new part serial number from request data.
     *
     * @param StorePartSerialNumberRequest $request
     *
     * @param Part $part
     *
     * @return PartSerialNumber
     */
    public function create(
        StorePartSerialNumberRequest $request,
        Part $part
    ): PartSerialNumber {
        $user = $request->user();
        return $part->serialNumbers()->create([
            ...$request->validated(),
            'created_by' => $user->id,
            'created_at' => now(),
        ]);
    }
}
