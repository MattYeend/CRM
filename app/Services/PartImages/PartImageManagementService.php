<?php

namespace App\Services\PartImages;

use App\Http\Requests\StorePartImageRequest;
use App\Http\Requests\UpdatePartImageRequest;
use App\Models\PartImage;

/**
 * Orchestrates part image lifecycle operations by delegating to focused
 * sub-services.
 *
 * Acts as the single entry point for part image create, update, delete, and
 * restore operations, keeping controllers decoupled from the underlying
 * service implementations.
 */
class PartImageManagementService
{
    /**
     * Service responsible for creating new part image records.
     *
     * @var PartImageCreatorService
     */
    private PartImageCreatorService $creator;

    /**
     * Service responsible for updating existing part image records.
     *
     * @var PartImageUpdaterService
     */
    private PartImageUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring part image records.
     *
     * @var PartImageDestructorService
     */
    private PartImageDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  PartImageCreatorService $creator Handles part image creation.
     * @param  PartImageUpdaterService $updater Handles part image updates.
     * @param  PartImageDestructorService $destructor Handles part image
     * deletion and restoration.
     */
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
     * @param  StorePartImageRequest $request Validated request containing
     * part image data.
     *
     * @return PartImage The newly created part image.
     */
    public function store(StorePartImageRequest $request): PartImage
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing part image.
     *
     * @param  UpdatePartImageRequest $request Validated request containing
     * updated part image data.
     * @param  PartImage $partImage The part image instance to update.
     *
     * @return PartImage The updated part image.
     */
    public function update(
        UpdatePartImageRequest $request,
        PartImage $partImage
    ): PartImage {
        return $this->updater->update($request, $partImage);
    }

    /**
     * Soft-delete a part image.
     *
     * Delegates to the destructor service to perform a soft-delete.
     *
     * @param  PartImage $partImage The part image instance to delete.
     *
     * @return void
     */
    public function destroy(PartImage $partImage): void
    {
        $this->destructor->destroy($partImage);
    }

    /**
     * Restore a soft-deleted part image.
     *
     * @param  int $id The primary key of the soft-deleted part image.
     *
     * @return PartImage The restored part image.
     */
    public function restore(int $id): PartImage
    {
        return $this->destructor->restore($id);
    }
}
