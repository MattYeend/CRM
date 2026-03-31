<?php

namespace App\Services\InvoiceItems;

use App\Http\Requests\StoreInvoiceItemRequest;
use App\Http\Requests\UpdateInvoiceItemRequest;
use App\Models\InvoiceItem;

/**
 * Central service for managing InvoiceItem records.
 *
 * Delegates creation, update, deletion, and restoration operations to
 * the respective creator, updater, and destructor services, providing
 * a unified interface for invoice item management.
 */
class InvoiceItemManagementService
{
    /**
     * Service responsible for creating new invoice item records.
     *
     * @var InvoiceItemCreatorService
     */
    private InvoiceItemCreatorService $creator;

    /**
     * Service responsible for updating existing invoice item records.
     *
     * @var InvoiceItemUpdaterService
     */
    private InvoiceItemUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring invoice item records.
     *
     * @var InvoiceItemDestructorService
     */
    private InvoiceItemDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  InvoiceItemCreatorService $creator Handles invoice item creation.
     * @param  InvoiceItemUpdaterService $updater Handles invoice item updates.
     * @param  InvoiceItemDestructorService $destructor Handles deletion
     * and restoration.
     */
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
     * Delegates to the creator service to validate and store the invoice item.
     *
     * @param  StoreInvoiceItemRequest $request The request containing invoice
     * item data.
     *
     * @return InvoiceItem The newly created invoice item instance.
     */
    public function store(StoreInvoiceItemRequest $request): InvoiceItem
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing InvoiceItem.
     *
     * Delegates to the updater service to modify the InvoiceItem data.
     *
     * @param  UpdateInvoiceItemRequest $request The request containing
     * updated invoice item data.
     * @param  InvoiceItem $InvoiceItem The InvoiceItem to update.
     *
     * @return InvoiceItem The updated InvoiceItem instance.
     */
    public function update(
        UpdateInvoiceItemRequest $request,
        InvoiceItem $invoiceItem
    ): InvoiceItem {
        return $this->updater->update($request, $invoiceItem);
    }

    /**
     * Soft-delete a invoiceItem.
     *
     * Delegates to the destructor service to perform a soft-delete.
     *
     * @param  InvoiceItem $invoiceItem The invoiceItem to delete.
     *
     * @return void
     */
    public function destroy(InvoiceItem $invoiceItem): void
    {
        $this->destructor->destroy($invoiceItem);
    }

    /**
     * Restore a soft-deleted invoiceItem.
     *
     * Delegates to the destructor service to restore the invoiceItem.
     *
     * @param  int $id The primary key of the soft-deleted invoiceItem.
     *
     * @return InvoiceItem The restored invoiceItem instance.
     */
    public function restore(int $id): InvoiceItem
    {
        return $this->destructor->restore($id);
    }
}
