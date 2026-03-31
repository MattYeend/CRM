<?php

namespace App\Services\Leads;

use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;

/**
 * Handles the creation of new Lead records.
 *
 * Extracts validated data from the request, stamps the creator and
 * creation timestamp, and persists the new Lead.
 */
class LeadCreatorService
{
    /**
     * Create a new lead from the validated request data.
     *
     * Sets the created_by and created_at audit fields from the authenticated
     * user before persisting the record.
     *
     * @param  StoreLeadRequest $request Validated request containing lead
     * data.
     *
     * @return Lead The newly created lead record.
     */
    public function create(StoreLeadRequest $request): Lead
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;
        $data['created_at'] = now();

        return Lead::create($data);
    }
}
