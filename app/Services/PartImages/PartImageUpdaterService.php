<?php

namespace App\Services\PartImages;

use App\Http\Requests\UpdatePartImageRequest;
use App\Models\PartImage;

class PartImageUpdaterService
{
    /**
     * Update the part image using request data.
     *
     * @param UpdatePartImageRequest $request
     *
     * @param PartImage $partImage
     *
     * @return PartImage
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
