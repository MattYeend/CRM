<?php

namespace App\Services\PartImages;

use App\Http\Requests\StorePartImageRequest;
use App\Models\PartImage;

/**
 * Handles the creation of new PartImage records.
 *
 * Stores the uploaded image file to the public disk, extracts validated
 * data from the request, stamps the creator and creation timestamp, and
 * persists the new PartImage.
 */
class PartImageCreatorService
{
    /**
     * Create a new part image from the validated request data.
     *
     * Stores the uploaded image file under the part-images directory on the
     * public disk, then sets the created_by and created_at audit fields from
     * the authenticated user before persisting the record.
     *
     * @param  StorePartImageRequest $request Validated request containing the
     * image file and part image data.
     *
     * @return PartImage The newly created part image record.
     */
    public function create(StorePartImageRequest $request): PartImage
    {
        $user = $request->user();
    $data = $request->validated();

    unset($data['image']);

    $storedPath = $request->file('image')->store('part-images', 'public');

    if ($storedPath === false) {
        throw new \RuntimeException('Failed to store image file.');
    }

    $data['image'] = $storedPath;
    $data['created_by'] = $user->id;

    return PartImage::create($data);

    }
}
