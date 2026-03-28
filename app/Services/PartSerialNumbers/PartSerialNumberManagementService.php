<?php

namespace App\Services\PartSerialNumbers;

use App\Http\Requests\StorePartSerialNumberRequest;
use App\Http\Requests\UpdatePartSerialNumberRequest;
use App\Models\Part;
use App\Models\PartSerialNumber;

class PartSerialNumberManagementService
{
    private PartSerialNumberCreatorService $creator;
    private PartSerialNumberUpdaterService $updater;
    private PartSerialNumberDestructorService $destructor;

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
     * @param StorePartSerialNumberRequest $request
     *
     * @param Part $part
     *
     * @return PartSerialNumber
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
     * @param UpdatePartSerialNumberRequest $request
     *
     * @param PartSerialNumber $partSerialNumber
     *
     * @return PartSerialNumber
     */
    public function update(
        UpdatePartSerialNumberRequest $request,
        PartSerialNumber $partSerialNumber
    ): PartSerialNumber {
        return $this->updater->update($request, $partSerialNumber);
    }

    /**
     * Delete a part serial number (soft delete).
     *
     * @param PartSerialNumber $partSerialNumber
     *
     * @return void
     */
    public function destroy(PartSerialNumber $partSerialNumber): void
    {
        $this->destructor->destroy($partSerialNumber);
    }

    /**
     * Restore a soft-deleted part serial number
     *
     * @param int $id
     *
     * @return PartSerialNumber
     */
    public function restore(int $id): PartSerialNumber
    {
        return $this->destructor->restore($id);
    }
}
