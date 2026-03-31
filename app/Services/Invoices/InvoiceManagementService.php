<?php

namespace App\Services\Invoices;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;

/**
 * Central service for managing Invoice records.
 *
 * Delegates creation, update, deletion, and restoration operations to
 * the respective creator, updater, and destructor services, providing
 * a unified interface for invoice management.
 */
class InvoiceManagementService
{
    /**
     * Service responsible for creating new invoice records.
     *
     * @var InvoiceCreatorService
     */
    private InvoiceCreatorService $creator;

    /**
     * Service responsible for updating existing invoice records.
     *
     * @var InvoiceUpdaterService
     */
    private InvoiceUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring invoice records.
     *
     * @var InvoiceDestructorService
     */
    private InvoiceDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  InvoiceCreatorService $creator Handles invoice creation.
     * @param  InvoiceUpdaterService $updater Handles invoice updates.
     * @param  InvoiceDestructorService $destructor Handles deletion
     * and restoration.
     */
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
     * Delegates to the creator service to validate and store the invoice.
     *
     * @param  StoreInvoiceRequest $request The request containing invoice
     * data.
     *
     * @return Invoice The newly created invoice instance.
     */
    public function store(StoreInvoiceRequest $request): Invoice
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing Invoice.
     *
     * Delegates to the updater service to modify the Invoice data.
     *
     * @param  UpdateInvoiceRequest $request The request containing
     * updated invoice data.
     * @param  Invoice $Invoice The Invoice to update.
     *
     * @return Invoice The updated Invoice instance.
     */
    public function update(
        UpdateInvoiceRequest $request,
        Invoice $invoice
    ): Invoice {
        return $this->updater->update($request, $invoice);
    }

    /**
     * Soft-delete a invoice.
     *
     * Delegates to the destructor service to perform a soft-delete.
     *
     * @param  Invoice $invoice The invoice to delete.
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
     * Delegates to the destructor service to restore the invoice.
     *
     * @param  int $id The primary key of the soft-deleted invoice.
     *
     * @return Invoice The restored invoice instance.
     */
    public function restore(int $id): Invoice
    {
        return $this->destructor->restore($id);
    }
}
