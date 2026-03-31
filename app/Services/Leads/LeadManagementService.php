<?php

namespace App\Services\Leads;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Lead;

/**
 * Orchestrates lead lifecycle operations by delegating to focused
 * sub-services.
 *
 * Acts as the single entry point for lead create, update, delete, and
 * restore operations, keeping controllers decoupled from the underlying
 * service implementations.
 */
class LeadManagementService
{
    /**
     * Service responsible for creating new lead records.
     *
     * @var LeadCreatorService
     */
    private LeadCreatorService $creator;

    /**
     * Service responsible for updating existing lead records.
     *
     * @var LeadUpdaterService
     */
    private LeadUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring lead records.
     *
     * @var LeadDestructorService
     */
    private LeadDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  LeadCreatorService $creator Handles lead creation.
     * @param  LeadUpdaterService $updater Handles lead updates.
     * @param  LeadDestructorService $destructor Handles lead deletion
     * and restoration.
     */
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
     * @param  StoreLeadRequest $request Validated request containing lead
     * data.
     *
     * @return Lead The newly created lead.
     */
    public function store(StoreLeadRequest $request): Lead
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing lead.
     *
     * @param  UpdateLeadRequest $request Validated request containing
     * updated lead data.
     * @param  Lead $lead The lead instance to update.
     *
     * @return Lead The updated lead.
     */
    public function update(
        UpdateLeadRequest $request,
        Lead $lead
    ): Lead {
        return $this->updater->update($request, $lead);
    }

    /**
     * Soft-delete a lead.
     *
     * Delegates to the destructor service to perform a soft-delete.
     *
     * @param  Lead $lead The lead to delete.
     *
     * @return void
     */
    public function destroy(Lead $lead): void
    {
        $this->destructor->destroy($lead);
    }

    /**
     * Restore a soft-deleted lead.
     *
     * @param  int $id The primary key of the soft-deleted lead.
     *
     * @return Lead The restored lead.
     */
    public function restore(int $id): Lead
    {
        return $this->destructor->restore($id);
    }
}
