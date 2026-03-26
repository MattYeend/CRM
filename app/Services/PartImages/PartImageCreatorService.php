<?php

namespace App\Services\PartImages;

use App\Http\Requests\StorePartImageRequest;
use App\Models\PartImage;

class PartImageCreatorService
{
    /**
     * Create a new part from request data.
     *
     * @param StorePartImageRequest $request
     *
     * @return PartImage
     */
    public function create(StorePartImageRequest $request): PartImage
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;
        $data['created_at'] = now();

        return PartImage::create($data);
    }
}
