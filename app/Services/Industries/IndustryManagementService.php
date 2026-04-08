<?php

namespace App\Services\Industries;

use App\Http\Requests\StoreIndustryRequest;
use App\Http\Requests\UpdateIndustryRequest;
use App\Models\Industry;

/**
 * Central service for managing Industry records.
 *
 * Delegates creation, update, deletion, and restoration operations to
 * the respective creator, updater, and destructor services, providing
 * a unified interface for industry management.
 */
class IndustryManagementService
{
    /**
     * Service responsible for creating new industry records.
     *
     * @var IndustryCreatorService
     */
    private IndustryCreatorService $creator;

    /**
     * Service responsible for updating existing industry records.
     *
     * @var IndustryUpdaterService
     */
    private IndustryUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring learning records.
     *
     * @var IndustryDestructorService
     */
    private IndustryDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  IndustryCreatorService $creator Handles industry creation.
     * @param  IndustryUpdaterService $updater Handles industry updates.
     * @param  IndustryDestructorService $destructor Handles deletion and
     * restoration.
     */
    public function __construct(
        IndustryCreatorService $creator,
        IndustryUpdaterService $updater,
        IndustryDestructorService $destructor
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new industry.
     *
     * Delegates to the creator service to validate and store the industry.
     *
     * @param  StoreIndustryRequest $request The request containing industry
     * data.
     *
     * @return Industry The newly created industry instance.
     */
    public function store(StoreIndustryRequest $request): Industry
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing industry.
     *
     * Delegates to the updater service to modify the industry data.
     *
     * @param  UpdateIndustryRequest $request The request containing
     * updated industry data.
     * @param  Industry $industry The industry to update.
     *
     * @return Industry The updated industry instance.
     */
    public function update(
        UpdateIndustryRequest $request,
        Industry $industry
    ): Industry {
        return $this->updater->update($request, $industry);
    }

    /**
     * Soft-delete a industry.
     *
     * Delegates to the destructor service to perform a soft-delete.
     *
     * @param  Industry $industry The industry to delete.
     *
     * @return void
     */
    public function destroy(Industry $industry): void
    {
        $this->destructor->destroy($industry);
    }

    /**
     * Restore a soft-deleted industry.
     *
     * Delegates to the destructor service to restore the industry.
     *
     * @param  int $id The primary key of the soft-deleted industry.
     *
     * @return Industry The restored industry instance.
     */
    public function restore(int $id): Industry
    {
        return $this->destructor->restore($id);
    }
}
