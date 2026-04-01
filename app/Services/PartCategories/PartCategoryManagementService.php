<?php

namespace App\Services\PartCategories;

use App\Http\Requests\StorePartCategoryRequest;
use App\Http\Requests\UpdatePartCategoryRequest;
use App\Models\PartCategory;

/**
 * Orchestrates part category lifecycle operations by delegating to focused
 * sub-services.
 *
 * Acts as the single entry point for part category create, update, delete, and
 * restore operations, keeping controllers decoupled from the underlying
 * service implementations.
 */
class PartCategoryManagementService
{
    /**
     * Service responsible for creating new part category records.
     *
     * @var PartCategoryCreatorService
     */
    private PartCategoryCreatorService $creator;

    /**
     * Service responsible for updating existing part category records.
     *
     * @var PartCategoryUpdaterService
     */
    private PartCategoryUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring part category
     * records.
     *
     * @var PartCategoryDestructorService
     */
    private PartCategoryDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  PartCategoryCreatorService $creator Handles part category
     * creation.
     * @param  PartCategoryUpdaterService $updater Handles part category
     * updates.
     * @param  PartCategoryDestructorService $destructor Handles part category
     * deletion and restoration.
     */
    public function __construct(
        PartCategoryCreatorService $creator,
        PartCategoryUpdaterService $updater,
        PartCategoryDestructorService $destructor
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new part category.
     *
     * @param  StorePartCategoryRequest $request Validated request containing
     * part category data.
     *
     * @return PartCategory The newly created part category.
     */
    public function store(StorePartCategoryRequest $request): PartCategory
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing part category.
     *
     * @param  UpdatePartCategoryRequest $request Validated request containing
     * updated part category data.
     * @param  PartCategory $partCategory The part category instance to update.
     *
     * @return PartCategory The updated part category.
     */
    public function update(
        UpdatePartCategoryRequest $request,
        PartCategory $partCategory
    ): PartCategory {
        return $this->updater->update($request, $partCategory);
    }

    /**
     * Soft-delete a part category.
     *
     * Delegates to the destructor service to perform a soft-delete.
     *
     * @param  PartCategory $partCategory The part category instance to delete.
     *
     * @return void
     */
    public function destroy(PartCategory $partCategory): void
    {
        $this->destructor->destroy($partCategory);
    }

    /**
     * Restore a soft-deleted part category.
     *
     * @param  int $id The primary key of the soft-deleted part category.
     *
     * @return PartCategory The restored part category.
     */
    public function restore(int $id): PartCategory
    {
        return $this->destructor->restore($id);
    }
}
