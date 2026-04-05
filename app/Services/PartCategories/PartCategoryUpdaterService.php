<?php

namespace App\Services\PartCategories;

use App\Http\Requests\UpdatePartCategoryRequest;
use App\Models\PartCategory;

/**
 * Handles updates to existing PartCategory records.
 *
 * Extracts validated data from the request, stamps the updater and update
 * timestamp, persists the changes, and returns a freshly reloaded instance.
 */
class PartCategoryUpdaterService
{
    /**
     * Update the part category using the validated request data.
     *
     * Sets the updated_by and updated_at audit fields from the authenticated
     * user before persisting the changes.
     *
     * @param  UpdatePartCategoryRequest $request Validated request containing
     * updated part category data.
     * @param  PartCategory $partCategory The part category instance to update.
     *
     * @return PartCategory The updated and freshly reloaded part category
     * instance.
     */
    public function update(
        UpdatePartCategoryRequest $request,
        PartCategory $partCategory
    ): PartCategory {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;

        $partCategory->update($data);

        return $partCategory->fresh();
    }
}
