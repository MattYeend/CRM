<?php

namespace App\Services\PartSerialNumbers;

use App\Http\Requests\UpdatePartSerialNumberRequest;
use App\Models\PartSerialNumber;

/**
 * Handles updates to existing PartSerialNumber records.
 *
 * Extracts validated data from the request, stamps the updater and update
 * timestamp, persists the changes, and returns a freshly reloaded instance.
 */
class PartSerialNumberUpdaterService
{
    /**
     * Update the part serial number using the validated request data.
     *
     * Sets the updated_by and updated_at audit fields from the authenticated
     * user before persisting the changes.
     *
     * @param  UpdatePartSerialNumberRequest $request Validated request
     * containing updated part serial number data.
     * @param  PartSerialNumber $partSerialNumber The part serial number
     * instance to update.
     *
     * @return PartSerialNumber The updated and freshly reloaded part serial
     * number instance.
     */
    public function update(
        UpdatePartSerialNumberRequest $request,
        PartSerialNumber $partSerialNumber
    ): PartSerialNumber {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;

        $partSerialNumber->update($data);

        return $partSerialNumber->fresh();
    }
}
