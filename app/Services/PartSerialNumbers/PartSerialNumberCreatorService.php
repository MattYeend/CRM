<?php

namespace App\Services\PartSerialNumbers;

use App\Http\Requests\StorePartSerialNumberRequest;
use App\Models\Part;
use App\Models\PartSerialNumber;

/**
 * Handles the creation of new PartSerialNumber records.
 *
 * Creates the serial number as a child of the given part via the
 * serialNumbers relationship, stamping the creator and creation timestamp
 * from the authenticated user.
 */
class PartSerialNumberCreatorService
{
    /**
     * Create a new part serial number from the validated request data.
     *
     * Persists the serial number against the given part's serialNumbers
     * relationship, setting the created_by and created_at audit fields from
     * the authenticated user.
     *
     * @param  StorePartSerialNumberRequest $request Validated request
     * containing part serial number data.
     * @param  Part $part The part to associate the new serial number with.
     *
     * @return PartSerialNumber The newly created part serial number record.
     */
    public function create(
        StorePartSerialNumberRequest $request,
        Part $part
    ): PartSerialNumber {
        $user = $request->user();
        return $part->serialNumbers()->create([
            ...$request->validated(),
            'created_by' => $user->id,
        ]);
    }
}
