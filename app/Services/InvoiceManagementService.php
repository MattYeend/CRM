<?php

namespace App\Services;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;

class InvoiceManagementService
{
    private InvoiceCreatorService $creator;
    private InvoiceUpdaterService $updater;
    private InvoiceDestructorService $destructor;

    public function __construct(
        InvoiceCreatorService $creator,
        InvoiceUpdaterService $updater,
        InvoiceDestructorService $destructor
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new invoice.
     *
     * @param StoreInvoiceRequest $request
     *
     * @return Invoice
     */
    public function store(StoreInvoiceRequest $request): Invoice
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing invoice.
     *
     * @param UpdateInvoiceRequest $request
     *
     * @param Invoice $invoice
     *
     * @return Invoice
     */
    public function update(
        UpdateInvoiceRequest $request,
        Invoice $invoice
    ): Invoice {
        return $this->updater->update($request, $invoice);
    }

    /**
     * Delete a invoice (soft delete).
     *
     * @param Invoice $invoice
     *
     * @return void
     */
    public function destroy(Invoice $invoice): void
    {
        $this->destructor->destroy($invoice);
    }

    /**
     * Restore a soft-deleted invoice.
     *
     * @param int $id
     *
     * @return Invoice
     */
    public function restore(int $id): Invoice
    {
        return $this->destructor->restore($id);
    }
}
