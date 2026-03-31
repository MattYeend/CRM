<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single invoice with related data loaded.
     *
     * @param  Invoice $invoice The route-model-bound invoice
     * instance.
     *
     * @return Invoice The invoice with relationships loaded.
     */
    public function show(Invoice $invoice): Invoice
    {
        return $invoice->load(
            'company',
            'items',
        );
    }
}
