<?php

namespace App\Services;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Lead;

class LeadManagementService
{
    private LeadCreatorService $creator;
    private LeadUpdaterService $updater;
    private LeadDestructorService $destructor;

    public function __construct(
        LeadCreatorService $creator,
        LeadUpdaterService $updater,
        LeadDestructorService $destructor
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new lead.
     *
     * @param StoreLeadRequest $request
     *
     * @return Lead
     */
    public function store(StoreLeadRequest $request): Lead
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing lead.
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
        return $this->updater->update($request, $lead);
    }

    /**
     * Delete a lead (soft delete).
     *
     * @param Lead $lead
     *
     * @return void
     */
    public function destroy(Lead $lead): void
    {
        $this->destructor->destroy($lead);
    }

    /**
     * Restore a soft-deleted lead
     *
     * @param int $id
     *
     * @return Lead
     */
    public function restore(int $id): Lead
    {
        return $this->destructor->restore($id);
    }
}
