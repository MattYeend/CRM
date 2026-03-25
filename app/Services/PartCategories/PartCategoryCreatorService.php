<?php

namespace App\Services\PartCategories;

use App\Http\Requests\StorePartCategoryRequest;
use App\Models\PartCategory;

class PartCategoryCreatorService
{
    /**
     * Create a new part from request data.
     *
     * @param StorePartCategoryRequest $request
     *
     * @return PartCategory
     */
    public function create(StorePartCategoryRequest $request): PartCategory
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;
        $data['created_at'] = now();

        return PartCategory::create($data);
    }
}
