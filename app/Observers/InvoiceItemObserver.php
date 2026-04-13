<?php

namespace App\Observers;

use App\Models\InvoiceItem;

/**
 * Observes lifecycle events on InvoiceItem records and keeps the parent
 * Invoice totals in sync whenever a line item is created, updated, deleted,
 * or restored.
 *
 * Each handler delegates to Invoice::recalculateTotals(), which re-sums all
 * non-deleted line totals and persists the updated subtotal and total via
 * saveQuietly() to avoid triggering further model events.
 *
 * Register this observer in AppServiceProvider:
 * ```php
 * InvoiceItem::observe(InvoiceItemObserver::class);
 * ```
 */
class InvoiceItemObserver
{
    /**
     * Handle the InvoiceItem "saved" event.
     *
     * Fired after a line item is created or updated. Triggers a recalculation
     * of the parent invoice's subtotal and total to reflect the new or changed
     * line total.
     *
     * @param  InvoiceItem $invoiceItem The invoice item that was saved.
     *
     * @return void
     */
    public function saved(InvoiceItem $invoiceItem): void
    {
        $invoiceItem->invoice?->recalculateTotals();
    }

    /**
     * Handle the InvoiceItem "deleted" event.
     *
     * Fired after a line item is soft-deleted. Triggers a recalculation of
     * the parent invoice's subtotal and total so that the deleted item's
     * value is no longer included in the invoice totals.
     *
     * @param  InvoiceItem $invoiceItem The invoice item that was deleted.
     *
     * @return void
     */
    public function deleted(InvoiceItem $invoiceItem): void
    {
        $invoiceItem->invoice?->recalculateTotals();
    }

    /**
     * Handle the InvoiceItem "restored" event.
     *
     * Fired after a soft-deleted line item is restored. Triggers a
     * recalculation of the parent invoice's subtotal and total so that
     * the restored item's value is included again.
     *
     * @param  InvoiceItem $invoiceItem The invoice item that was restored.
     *
     * @return void
     */
    public function restored(InvoiceItem $invoiceItem): void
    {
        $invoiceItem->invoice?->recalculateTotals();
    }
}
