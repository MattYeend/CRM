<?php

namespace App\Services\Leads;

use App\Http\Requests\UpdateLeadRequest;
use App\Models\Lead;

class LeadUpdaterService
{
    /**
     * Update the lead using request data.
     *
     * @param UpdateLeadRequest $request
     *
     * @param Lead $lead
     *
     * @return Lead
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
