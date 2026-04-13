<?php

namespace App\Services\InvoiceItems;

use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
     * @return array Paginated invoice item results with top-level permissions.
     */
    public function list(Request $request): array
    {
        $query = InvoiceItem::with('invoice', 'product');

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $this->paginate($query, $request);

        return array_merge(
            $paginator,
            ['permissions' => $this->getPermissions()]
        );
    }

    /**
     * Return a single invoice item with related data loaded.
     *
     * @param  InvoiceItem $invoiceItem The route-model-bound invoice
     * item instance.
     *
     * @return array
     */
    public function show(InvoiceItem $invoiceItem): array
    {
        $invoiceItem->load(
            'invoice',
            'product',
        );

        return $this->formatInvoiceItem($invoiceItem);
    }

    /**
     * Paginate and transform the invoice item query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  Request $request
     *
     * @return array
     */
    private function paginate($query, Request $request): array
    {
        $perPage = max(1, min((int) $request->query('per_page', 10), 100));

        return $query->paginate($perPage)
            ->appends($request->query())
            ->through(
                fn (InvoiceItem $item): array => $this->formatInvoiceItem($item)
            )
            ->toArray();
    }

    /**
     * Get permission flags for the current user.
     *
     * @return array
     */
    private function getPermissions(): array
    {
        return [
            'create' => Gate::allows('create', InvoiceItem::class),
            'viewAny' => Gate::allows('viewAny', InvoiceItem::class),
        ];
    }

    /**
     * Format an invoice item into a structured array.
     *
     * Includes core attributes, related data, derived accessors,
     * and authorisation permissions for the current user.
     *
     * @param  InvoiceItem $item
     *
     * @return array
     */
    private function formatInvoiceItem(InvoiceItem $item): array
    {
        return [
            'id' => $item->id,
            'invoice_id' => $item->invoice_id,
            'description' => $item->description,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'formatted_unit_price' => $item->formatted_unit_price,
            'line_total' => $item->line_total,
            'formatted_line_total' => $item->formatted_line_total,
            'has_product' => $item->has_product,
            'invoice' => $item->invoice,
            'product' => $item->product,
            'creator' => $item->creator,
            'permissions' => [
                'view' => Gate::allows('view', $item),
                'update' => Gate::allows('update', $item),
                'delete' => Gate::allows('delete', $item),
            ],
        ];
    }
}
