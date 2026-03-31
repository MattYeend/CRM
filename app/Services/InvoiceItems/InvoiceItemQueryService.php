<?php

namespace App\Services\InvoiceItems;

use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Handles read queries for InvoiceItem records.
 *
 * Delegates searching, sorting, and trash filtering to dedicated
 * sub-services and returns paginated or single invoice item results with
 * the appropriate relationships loaded.
 */
class InvoiceItemQueryService
{
    /**
     * Service responsible for applying sort order.
     *
     * @var InvoiceItemSortingService
     */
    private InvoiceItemSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters.
     *
     * @var InvoiceItemTrashFilterService
     */
    private InvoiceItemTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  InvoiceItemSortingService $sorting Handles sort order.
     * @param  InvoiceItemTrashFilterService $trashFilter Handles
     * trash filtering.
     */
    public function __construct(
        InvoiceItemSortingService $sorting,
        InvoiceItemTrashFilterService $trashFilter
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of invoice items with search, sorting,
     * and trash filters applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry search,
     * sort, filter, and pagination params.
     *
     * @return LengthAwarePaginator Paginated invoice item results.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(1, min((int) $request->query('per_page', 10), 100));

        $query = InvoiceItem::with('invoice', 'product');

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single invoice item with related data loaded.
     *
     * @param  InvoiceItem $invoiceItem The route-model-bound invoice
     * item instance.
     *
     * @return InvoiceItem The invoice item with relationships loaded.
     */
    public function show(InvoiceItem $invoiceItem): InvoiceItem
    {
        return $invoiceItem->load('invoice', 'product');
    }
}
