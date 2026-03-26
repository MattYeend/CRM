<?php

namespace App\Services\PartImages;

use App\Http\Requests\StorePartImageRequest;
use App\Http\Requests\UpdatePartImageRequest;
use App\Models\PartImage;

class PartImageManagementService
{
    private PartImageCreatorService $creator;
    private PartImageUpdaterService $updater;
    private PartImageDestructorService $destructor;

    public function __construct(
        PartImageCreatorService $creator,
        PartImageUpdaterService $updater,
        PartImageDestructorService $destructor
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new part image.
     *
     * @param StorePartImageRequest $request
     *
     * @return PartImage
     */
    public function store(StorePartImageRequest $request): PartImage
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing part image.
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
        return $this->updater->update($request, $partImage);
    }

    /**
     * Delete a part image (soft delete).
     *
     * @param PartImage $partImage
     *
     * @return void
     */
    public function destroy(PartImage $partImage): void
    {
        $this->destructor->destroy($partImage);
    }

    /**
     * Restore a soft-deleted part image
     *
     * @param int $id
     *
     * @return PartImage
     */
    public function restore(int $id): PartImage
    {
        return $this->destructor->restore($id);
    }
}

