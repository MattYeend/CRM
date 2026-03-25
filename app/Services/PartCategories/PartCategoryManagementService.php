<?php

namespace App\Services\PartCategories;

use App\Http\Requests\StorePartCategoryRequest;
use App\Http\Requests\UpdatePartCategoryRequest;
use App\Models\PartCategory;

class PartCategoryManagementService
{
    private PartCategoryCreatorService $creator;
    private PartCategoryUpdaterService $updater;
    private PartCategoryDestructorService $destructor;

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
     * @param StorePartCategoryRequest $request
     *
     * @return PartCategory
     */
    public function store(StorePartCategoryRequest $request): PartCategory
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing part category.
     *
     * @param UpdatePartCategoryRequest $request
     *
     * @param PartCategory $partCategory
     *
     * @return PartCategory
     */
    public function update(
        UpdatePartCategoryRequest $request,
        PartCategory $partCategory
    ): PartCategory {
        return $this->updater->update($request, $partCategory);
    }

    /**
     * Delete a part category (soft delete).
     *
     * @param PartCategory $partCategory
     *
     * @return void
     */
    public function destroy(PartCategory $partCategory): void
    {
        $this->destructor->destroy($partCategory);
    }

    /**
     * Restore a soft-deleted part category
     *
     * @param int $id
     *
     * @return PartCategory
     */
    public function restore(int $id): PartCategory
    {
        return $this->destructor->restore($id);
    }
}
