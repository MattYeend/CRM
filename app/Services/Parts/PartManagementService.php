<?php

namespace App\Services\Parts;

use App\Http\Requests\StorePartRequest;
use App\Http\Requests\UpdatePartRequest;
use App\Models\Part;

/**
 * Orchestrates part lifecycle operations by delegating to focused
 * sub-services.
 *
 * Acts as the single entry point for part create, update, delete, and
 * restore operations, keeping controllers decoupled from the underlying
 * service implementations.
 */
class PartManagementService
{
    /**
     * Service responsible for creating new part records.
     *
     * @var PartCreatorService
     */
    private PartCreatorService $creator;

    /**
     * Service responsible for updating existing part records.
     *
     * @var PartUpdaterService
     */
    private PartUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring part records.
     *
     * @var PartDestructorService
     */
    private PartDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  PartCreatorService $creator Handles part creation.
     * @param  PartUpdaterService $updater Handles part updates.
     * @param  PartDestructorService $destructor Handles part deletion and
     * restoration.
     */
    public function __construct(
        PartCreatorService $creator,
        PartUpdaterService $updater,
        PartDestructorService $destructor
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new part.
     *
     * @param  StorePartRequest $request Validated request containing part
     * data.
     *
     * @return Part The newly created part.
     */
    public function store(StorePartRequest $request): Part
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing part.
     *
     * @param  UpdatePartRequest $request Validated request containing updated
     * part data.
     * @param  Part $part The part instance to update.
     *
     * @return Part The updated part.
     */
    public function update(
        UpdatePartRequest $request,
        Part $part
    ): Part {
        return $this->updater->update($request, $part);
    }

    /**
     * Soft-delete a part.
     *
     * Delegates to the destructor service to perform a soft-delete.
     *
     * @param  Part $part The part instance to delete.
     *
     * @return void
     */
    public function destroy(Part $part): void
    {
        $this->destructor->destroy($part);
    }

    /**
     * Restore a soft-deleted part.
     *
     * @param  int $id The primary key of the soft-deleted part.
     *
     * @return Part The restored part.
     */
    public function restore(int $id): Part
    {
        return $this->destructor->restore($id);
    }
}
