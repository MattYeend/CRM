<?php

namespace App\Services\Industries;

use App\Http\Requests\StoreIndustryRequest;
use App\Models\Industry;

/**
 * Handles creation of Industry records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * a new industry record.
 */
class IndustryCreatorService
{
    /**
     * Create a new industry.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user and timestamp to audit fields, and creates the industry record.
     *
     * @param  StoreIndustryRequest $request The request containing validated
     * industry data.
     *
     * @return Industry The newly created industry instance.
     */
    public function create(StoreIndustryRequest $request): Industry
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;

        return Industry::create($data);
    }
}
