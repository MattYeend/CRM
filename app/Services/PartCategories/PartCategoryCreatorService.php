<?php

namespace App\Services\PartCategories;

use App\Http\Requests\StorePartCategoryRequest;
use App\Models\PartCategory;

/**
 * Handles the creation of new PartCategory records.
 *
 * Extracts validated data from the request, stamps the creator and
 * creation timestamp, and persists the new Part Category.
 */
class PartCategoryCreatorService
{
    /**
     * Create a new part category from the validated request data.
     *
     * Sets the created_by and created_at audit fields from the authenticated
     * user before persisting the record.
     *
     * @param  StorePartCategoryRequest $request Validated request containing
     * part category data.
     *
     * @return PartCategory The newly created part category record.
     */
    public function create(StorePartCategoryRequest $request): PartCategory
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;

        return PartCategory::create($data);
    }
}
