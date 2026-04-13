<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use Illuminate\Http\Request;
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
     * @param  InvoiceTrashFilterService $trashFilter Handles trash filtering.
     */
    public function __construct(
        InvoiceSortingService $sorting,
        InvoiceTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of invoices with sorting and trash filters
     * applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry search,
     * sort, filter, and pagination params.
     *
     * @return array Paginated invoice results with top-level permissions.
     */
    public function list(Request $request): array
    {
        $query = Invoice::with('company', 'items');

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $this->paginate($query, $request);

        return array_merge(
            $paginator,
            ['permissions' => $this->getPermissions()]
        );
    }

    /**
     * Return a single invoice with related data loaded.
     *
     * @param  Invoice $invoice The route-model-bound invoice instance.
     *
     * @return array
     */
    public function show(Invoice $invoice): array
    {
        $invoice->load('company', 'items');

        return $this->formatInvoice($invoice);
    }

    /**
     * Paginate and transform the invoice query.
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
                fn (Invoice $invoice): array => $this->formatInvoice($invoice)
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
            'create' => Gate::allows('create', Invoice::class),
            'viewAny' => Gate::allows('viewAny', Invoice::class),
        ];
    }

    /**
     * Format an invoice into a structured array.
     *
     * Combines core attributes, derived flags, relationships, and permissions.
     *
     * @param  Invoice $invoice
     *
     * @return array
     */
    private function formatInvoice(Invoice $invoice): array
    {
        return array_merge(
            $this->baseData($invoice),
            $this->derivedData($invoice),
            $this->relationshipData($invoice),
            $this->permissionData($invoice),
        );
    }

    /**
     * Extract core invoice attributes.
     *
     * @param  Invoice $invoice
     *
     * @return array
     */
    private function baseData(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'number' => $invoice->number,
            'status' => $invoice->status,
            'currency' => $invoice->currency,
            'subtotal' => $invoice->subtotal,
            'formatted_subtotal' => $invoice->formatted_subtotal,
            'tax' => $invoice->tax,
            'formatted_tax' => $invoice->formatted_tax,
            'total' => $invoice->total,
            'formatted_total' => $invoice->formatted_total,
            'issue_date' => $invoice->issue_date,
            'due_date' => $invoice->due_date,
        ];
    }

    /**
     * Extract derived invoice flags.
     *
     * @param  Invoice $invoice
     *
     * @return array
     */
    private function derivedData(Invoice $invoice): array
    {
        return [
            'is_draft' => $invoice->is_draft,
            'is_sent' => $invoice->is_sent,
            'is_paid' => $invoice->is_paid,
            'is_overdue' => $invoice->is_overdue,
            'is_cancelled' => $invoice->is_cancelled,
        ];
    }

    /**
     * Extract related model data for the invoice.
     *
     * @param  Invoice $invoice
     *
     * @return array
     */
    private function relationshipData(Invoice $invoice): array
    {
        return [
            'company' => $invoice->company,
            'items' => $invoice->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'formatted_unit_price' => $item->formatted_unit_price ??
                        number_format($item->unit_price, 2),
                    'formatted_line_total' => $item->formatted_line_total ??
                        number_format($item->line_total, 2),
                    'product' => $item->product ? [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                    ] : null,
                ];
            }),
            'creator' => $invoice->creator,
        ];
    }

    /**
     * Determine authorisation permissions for the invoice.
     *
     * @param  Invoice $invoice
     *
     * @return array
     */
    private function permissionData(Invoice $invoice): array
    {
        return [
            'permissions' => [
                'view' => Gate::allows('view', $invoice),
                'update' => Gate::allows('update', $invoice),
                'delete' => Gate::allows('delete', $invoice),
            ],
        ];
    }
}
