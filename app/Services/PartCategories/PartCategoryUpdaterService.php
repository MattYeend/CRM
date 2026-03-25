<?php

namespace App\Services\PartCategories;

use App\Http\Requests\UpdatePartCategoryRequest;
use App\Models\PartCategory;

class PartCategoryUpdaterService
{
    /**
     * Update the part category using request data.
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
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        $partCategory->update($data);

        return $partCategory->fresh();
    }
}
