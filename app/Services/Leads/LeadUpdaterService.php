<?php

namespace App\Services\Leads;

use App\Http\Requests\UpdateLeadRequest;
use App\Models\Lead;

/**
 * Handles updates to Lead records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * updates to the lead.
 */
class LeadUpdaterService
{
    /**
     * Update an existing lead.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user and timestamp to audit fields, updates the lead, and returns
     * a fresh instance.
     *
     * @param  UpdateLeadRequest $request The request containing
     * validated lead data.
     * @param  Lead $lead The lead to update.
     *
     * @return Lead The updated lead instance.
     */
    public function update(
        UpdateLeadRequest $request,
        Lead $lead
    ): Lead {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;

        $lead->update($data);

        return $lead->fresh();
    }
}
