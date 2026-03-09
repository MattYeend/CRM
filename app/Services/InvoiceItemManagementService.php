<?php

namespace App\Services;

use App\Http\Requests\StoreInvoiceItemRequest;
use App\Http\Requests\UpdateInvoiceItemRequest;
use App\Models\InvoiceItem;

class InvoiceItemManagementService
{
    private InvoiceItemCreatorService $creator;
    private InvoiceItemUpdaterService $updater;
    private InvoiceItemDestructorService $destructor;

    public function __construct(
        InvoiceItemCreatorService $creator,
        InvoiceItemUpdaterService $updater,
        InvoiceItemDestructorService $destructor
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new invoice item.
     *
     * @param StoreInvoiceItemRequest $request
     *
     * @return InvoiceItem
     */
    public function store(StoreInvoiceItemRequest $request): InvoiceItem
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing invoice item.
     *
     * @param UpdateInvoiceItemRequest $request
     *
     * @param InvoiceItem $invoiceItem
     *
     * @return InvoiceItem
     */
    public function update(
        UpdateInvoiceItemRequest $request,
        InvoiceItem $invoiceItem
    ): InvoiceItem {
        return $this->updater->update($request, $invoiceItem);
    }

    /**
     * Delete a invoice item (soft delete).
     *
     * @param InvoiceItem $invoiceItem
     *
     * @return void
     */
    public function destroy(InvoiceItem $invoiceItem): void
    {
        $this->destructor->destroy($invoiceItem);
    }

    /**
     * Restore a soft-deleted invoice item.
     *
     * @param int $id
     *
     * @return InvoiceItem
     */
    public function restore(int $id): InvoiceItem
    {
        return $this->destructor->restore($id);
    }
}
