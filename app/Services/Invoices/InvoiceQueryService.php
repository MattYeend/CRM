<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceQueryService
{
    private InvoiceSortingService $sorting;
    private InvoiceTrashFilterService $trashFilter;
    public function __construct(
        InvoiceSortingService $sorting,
        InvoiceTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated invoices, applying filters/sorting.
     *
     * @param Request $request
     *
     * @return LengthAwarePaginator
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = Invoice::with(
            'company',
            'contact',
            'items',
        );

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single invoice.
     *
     * @param Invoice $invoice
     *
     * @return Invoice
     */
    public function show(Invoice $invoice): Invoice
    {
        return $invoice->load(
            'company',
            'contact',
            'items',
        );
    }
}
