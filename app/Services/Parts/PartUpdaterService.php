<?php

namespace App\Services\Parts;

use App\Http\Requests\UpdatePartRequest;
use App\Models\Part;

/**
 * Handles updates to existing Part records.
 *
 * Extracts validated data from the request, stamps the updater and update
 * timestamp, persists the changes, and returns a freshly reloaded instance.
 */
class PartUpdaterService
{
    /**
     * Update the part using the validated request data.
     *
     * Sets the updated_by and updated_at audit fields from the authenticated
     * user before persisting the changes.
     *
     * @param  UpdatePartRequest $request Validated request containing updated
     * part data.
     * @param  Part $part The part instance to update.
     *
     * @return Part The updated and freshly reloaded part instance.
     */
    public function update(
        UpdatePartRequest $request,
        Part $part
    ): Part {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;

        $part->update($data);

        return $part->fresh();
    }
}
