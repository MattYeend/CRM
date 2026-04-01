<?php

namespace App\Services\PartImages;

use App\Http\Requests\UpdatePartImageRequest;
use App\Models\PartImage;

/**
 * Handles updates to existing PartImage records.
 *
 * Extracts validated data from the request, stamps the updater and update
 * timestamp, persists the changes, and returns a freshly reloaded instance.
 */
class PartImageUpdaterService
{
    /**
     * Update the part image using the validated request data.
     *
     * Sets the updated_by and updated_at audit fields from the authenticated
     * user before persisting the changes.
     *
     * @param  UpdatePartImageRequest $request Validated request containing
     * updated part image data.
     * @param  PartImage $partImage The part image instance to update.
     *
     * @return PartImage The updated and freshly reloaded part image instance.
     */
    public function update(
        UpdatePartImageRequest $request,
        PartImage $partImage
    ): PartImage {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        $partImage->update($data);

        return $partImage->fresh();
    }
}
