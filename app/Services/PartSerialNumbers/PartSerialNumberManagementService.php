<?php

namespace App\Services\PartSerialNumbers;

use App\Http\Requests\StorePartSerialNumberRequest;
use App\Http\Requests\UpdatePartSerialNumberRequest;
use App\Models\Part;
use App\Models\PartSerialNumber;

/**
 * Orchestrates part serial number lifecycle operations by delegating to
 * focused sub-services.
 *
 * Acts as the single entry point for part serial number create, update,
 * delete, and restore operations, keeping controllers decoupled from the
 * underlying service implementations.
 */
class PartSerialNumberManagementService
{
    /**
     * Service responsible for creating new part serial number records.
     *
     * @var PartSerialNumberCreatorService
     */
    private PartSerialNumberCreatorService $creator;

    /**
     * Service responsible for updating existing part serial number records.
     *
     * @var PartSerialNumberUpdaterService
     */
    private PartSerialNumberUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring part serial number
     * records.
     *
     * @var PartSerialNumberDestructorService
     */
    private PartSerialNumberDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  PartSerialNumberCreatorService $creator Handles part serial
     * number creation.
     * @param  PartSerialNumberUpdaterService $updater Handles part serial
     * number updates.
     * @param  PartSerialNumberDestructorService $destructor Handles part
     * serial number deletion and restoration.
     */
    public function __construct(
        PartSerialNumberCreatorService $creator,
        PartSerialNumberUpdaterService $updater,
        PartSerialNumberDestructorService $destructor
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new part serial number.
     *
     * @param  StorePartSerialNumberRequest $request Validated request
     * containing part serial number data.
     * @param  Part $part The part to associate the new serial number with.
     *
     * @return PartSerialNumber The newly created part serial number.
     */
    public function store(
        StorePartSerialNumberRequest $request,
        Part $part
    ): PartSerialNumber {
        return $this->creator->create($request, $part);
    }

    /**
     * Update an existing part serial number.
     *
     * @param  UpdatePartSerialNumberRequest $request Validated request
     * containing updated part serial number data.
     * @param  PartSerialNumber $partSerialNumber The part serial number
     * instance to update.
     *
     * @return PartSerialNumber The updated part serial number.
     */
    public function update(
        UpdatePartSerialNumberRequest $request,
        PartSerialNumber $partSerialNumber
    ): PartSerialNumber {
        return $this->updater->update($request, $partSerialNumber);
    }

    /**
     * Soft-delete a part serial number.
     *
     * Delegates to the destructor service to perform a soft-delete.
     *
     * @param  PartSerialNumber $partSerialNumber The part serial number
     * instance to delete.
     *
     * @return void
     */
    public function destroy(PartSerialNumber $partSerialNumber): void
    {
        $this->destructor->destroy($partSerialNumber);
    }

    /**
     * Restore a soft-deleted part serial number.
     *
     * @param  int $id The primary key of the soft-deleted part serial number.
     *
     * @return PartSerialNumber The restored part serial number.
     */
    public function restore(int $id): PartSerialNumber
    {
        return $this->destructor->restore($id);
    }
}
