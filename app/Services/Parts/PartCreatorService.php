<?php

namespace App\Services\Parts;

use App\Http\Requests\StorePartRequest;
use App\Models\Part;

/**
 * Handles the creation of new Part records.
 *
 * Extracts validated data from the request, stamps the creator and
 * creation timestamp, and persists the new Part.
 */
class PartCreatorService
{
    /**
     * Create a new part from the validated request data.
     *
     * Sets the created_by and created_at audit fields from the authenticated
     * user before persisting the record.
     *
     * @param  StorePartRequest $request Validated request containing part data.
     *
     * @return Part The newly created part record.
     */
    public function create(StorePartRequest $request): Part
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;

        return Part::create($data);
    }
}
