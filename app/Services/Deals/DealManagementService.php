<?php

namespace App\Services\Deals;

use App\Http\Requests\StoreDealRequest;
use App\Http\Requests\UpdateDealRequest;
use App\Models\Deal;

class DealManagementService
{
    private DealCreatorService $creator;
    private DealUpdaterService $updater;
    private DealDestructorService $destructor;

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
     * @param StoreDealRequest $request
     *
     * @return Deal
     */
    public function store(StoreDealRequest $request): Deal
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing deal.
     *
     * @param UpdateDealRequest $request
     *
     * @param Deal $deal
     *
     * @return Deal
     */
    public function update(
        UpdateDealRequest $request,
        Deal $deal
    ): Deal {
        return $this->updater->update($request, $deal);
    }

    /**
     * Delete a deal (soft delete).
     *
     * @param Deal $deal
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
     * @param int $id
     *
     * @return Deal
     */
    public function restore(int $id): Deal
    {
        return $this->destructor->restore($id);
    }
}
