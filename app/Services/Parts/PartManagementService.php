<?php

namespace App\Services\Parts;

use App\Http\Requests\StorePartRequest;
use App\Http\Requests\UpdatePartRequest;
use App\Models\Part;

class PartManagementService
{
    private PartCreatorService $creator;
    private PartUpdaterService $updater;
    private PartDestructorService $destructor;

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
     * @param StorePartRequest $request
     *
     * @return Lead
     */
    public function store(StorePartRequest $request): Part
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing part.
     *
     * @param UpdatePartRequest $request
     *
     * @param Part $part
     *
     * @return Part
     */
    public function update(
        UpdatePartRequest $request,
        Part $part
    ): Part {
        return $this->updater->update($request, $part);
    }

    /**
     * Delete a part (soft delete).
     *
     * @param Part $part
     *
     * @return void
     */
    public function destroy(Part $part): void
    {
        $this->destructor->destroy($part);
    }

    /**
     * Restore a soft-deleted part
     *
     * @param int $id
     *
     * @return Part
     */
    public function restore(int $id): Part
    {
        return $this->destructor->restore($id);
    }
}
