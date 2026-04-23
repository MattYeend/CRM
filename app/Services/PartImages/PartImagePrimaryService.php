<?php

namespace App\Services\PartImages;

use App\Models\PartImage;

/**
 * Handles enforcement of business rules for part images.
 *
 * Ensures that only one image per part can be marked as the primary image.
 * When an image is saved with `is_primary = true`, all other images for the
 * same part are automatically demoted to `is_primary = false`.
 *
 * This service is intended to be used from model observers, events, or
 * lifecycle hooks (e.g. within the PartImage model's saving event) to keep
 * business logic out of the model layer.
 *
 * Example usage:
 * ```php
 * $service = app(PartImagePrimaryService::class);
 * $service->enforcePrimary($image);
 * ```
 */
class PartImagePrimaryService
{
    /**
     * Ensure only one primary image exists per part.
     *
     * If the given image is marked as primary, this method will demote all
     * other images belonging to the same part by setting `is_primary` to false.
     *
     * If the image is not marked as primary, no action is taken.
     *
     * @param  PartImage $image The image being saved or updated.
     *
     * @return void
     */
    public function enforcePrimary(PartImage $image): void
    {
        if (! $image->is_primary) {
            return;
        }

        PartImage::where('part_id', $image->part_id)
            ->where('id', '!=', $image->id ?? 0)
            ->update(['is_primary' => false]);
    }
}
