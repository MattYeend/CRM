<?php

namespace App\Services\Deals;

use App\Http\Requests\StoreDealRequest;
use App\Http\Requests\UpdateDealRequest;
use App\Models\Deal;

/**
 * Central service for managing Deal records.
 *
 * Delegates creation, update, deletion, and restoration operations to
 * the respective creator, updater, and destructor services, providing
 * a unified interface for deal management.
 */
class DealManagementService
{
    private DealCreatorService $creator;
    private DealUpdaterService $updater;
    private DealDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  DealCreatorService $creator Handles deal creation.
     * @param  DealUpdaterService $updater Handles deal updates.
     * @param  DealDestructorService $destructor Handles deletion
     * and restoration.
     */
    public function __construct(
        DealCreatorService $creator,
        DealUpdaterService $updater,
        DealDestructorService $destructor
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new deal.
     *
     * Delegates to the creator service to validate and store the deal.
     *
     * @param  StoreDealRequest $request The request containing deal data.
     *
     * @return Deal The newly created deal instance.
     */
    public function store(StoreDealRequest $request): Deal
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing deal.
     *
     * Delegates to the updater service to modify the deal data.
     *
     * @param  UpdateDealRequest $request The request containing
     * updated deal data.
     * @param  Deal $deal The deal to update.
     *
     * @return Deal The updated deal instance.
     */
    public function update(
        UpdateDealRequest $request,
        Deal $deal
    ): Deal {
        return $this->updater->update($request, $deal);
    }

    /**
     * Soft-delete a deal.
     *
     * Delegates to the destructor service to perform a soft-delete.
     *
     * @param  Deal $deal The deal to delete.
     *
     * @return void
     */
    public function destroy(Deal $deal): void
    {
        $this->destructor->destroy($deal);
    }

    /**
     * Restore a soft-deleted deal.
     *
     * Delegates to the destructor service to restore the deal.
     *
     * @param  int $id The primary key of the soft-deleted deal.
     *
     * @return Deal The restored deal instance.
     */
    public function restore(int $id): Deal
    {
        return $this->destructor->restore($id);
    }
}
