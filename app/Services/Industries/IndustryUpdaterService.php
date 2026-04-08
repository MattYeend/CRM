<?php

namespace App\Services\Industries;

use App\Http\Requests\UpdateIndustryRequest;
use App\Models\Industry;

/**
 * Handles updates to Industry records.
 *
 * Validates incoming data, assigns audit fields, and persists updates
 * to the industry record.
 */
class IndustryUpdaterService
{
    /**
     * Update an existing industry.
     *
     * Applies validated request data, records the authenticated user
     * and timestamp in audit fields, and returns a fresh instance of
     * the updated industry.
     *
     * @param  UpdateIndustryRequest $request The request containing
     * validated update data.
     * @param  Industry $industry The industry to update.
     *
     * @return Industry The updated industry instance.
     */
    public function update(
        UpdateIndustryRequest $request,
        Industry $industry
    ): Industry {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;

        $industry->update($data);

        return $industry->fresh();
    }
}
