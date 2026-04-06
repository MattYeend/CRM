<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

/**
 * Handles read queries for Invoice records.
 *
 * Delegates searching, sorting, and trash filtering to dedicated
 * sub-services and returns paginated or single invoice results with
 * the appropriate relationships loaded.
 */
class InvoiceQueryService
{
    /**
     * Service responsible for applying sort order.
     *
     * @var InvoiceSortingService
     */
    private InvoiceSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters.
     *
     * @var InvoiceTrashFilterService
     */
    private InvoiceTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  InvoiceSortingService $sorting Handles sort order.
     * @param  InvoiceTrashFilterService $trashFilter Handles
     * trash filtering.
     */
    public function __construct(
        InvoiceSortingService $sorting,
        InvoiceTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of invoice with search, sorting,
     * and trash filters applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry search,
     * sort, filter, and pagination params.
     *
     * @return LengthAwarePaginator Paginated invoice results.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = Invoice::with(
            'company',
            'items',
        );

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $query->paginate($perPage)->appends($request->query());

        $paginator->through(
            fn (Invoice $invoice) => $this->formatInvoice($invoice)
        );

        $paginator->appends([
            'permissions' => [
                'create' => Gate::allows('create', Invoice::class),
                'viewAny' => Gate::allows('viewAny', Invoice::class),
            ],
        ]);

        return $paginator;
    }

    /**
     * Return a single invoice with related data loaded.
     *
     * @param  Invoice $invoice The route-model-bound invoice
     * instance.
     *
     * @return array
     */
    public function show(Invoice $invoice): array
    {
        $invoice->load(
            'company',
            'items',
        );

        return $this->formatInvoice($invoice);
    }

    /**
     * Format an invoice into a structured array.
     *
     * Includes core attributes, related data, derived accessors,
     * and authorisation permissions for the current user.
     *
     * @param  Invoice $invoice
     *
     * @return array
     */
    private function formatInvoice(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'number' => $invoice->number,
            'status' => $invoice->status,
            'is_draft' => $invoice->is_draft,
            'is_sent' => $invoice->is_sent,
            'is_paid' => $invoice->is_paid,
            'is_overdue' => $invoice->is_overdue,
            'is_cancelled' => $invoice->is_cancelled,
            'currency' => $invoice->currency,
            'subtotal' => $invoice->subtotal,
            'formatted_subtotal' => $invoice->formatted_subtotal,
            'tax' => $invoice->tax,
            'formatted_tax' => $invoice->formatted_tax,
            'total' => $invoice->total,
            'formatted_total' => $invoice->formatted_total,
            'issue_date' => $invoice->issue_date,
            'due_date' => $invoice->due_date,
            'company' => $invoice->company,
            'items' => $invoice->items,
            'creator' => $invoice->creator,
            'permissions' => [
                'view' => Gate::allows('view', $invoice),
                'update' => Gate::allows('update', $invoice),
                'delete' => Gate::allows('delete', $invoice),
            ],
        ];
    }
}
