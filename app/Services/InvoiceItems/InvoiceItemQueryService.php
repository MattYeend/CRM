<?php

namespace App\Services\InvoiceItems;

use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceItemQueryService
{
    private InvoiceItemSortingService $sorting;
    private InvoiceItemTrashFilterService $trashFilter;
    public function __construct(
        InvoiceItemSortingService $sorting,
        InvoiceItemTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated invoice items, applying filters/sorting.
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

        $query = InvoiceItem::with(
            'invoice',
            'product',
        );

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single invoice item.
     *
     * @param InvoiceItem $invoiceItem
     *
     * @return InvoiceItem
     */
    public function show(InvoiceItem $invoiceItem): InvoiceItem
    {
        return $invoiceItem->load(
            'invoice',
            'product',
        );
    }
}
