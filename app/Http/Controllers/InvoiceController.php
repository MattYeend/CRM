<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\InvoiceLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    /**
     * Declare a protected property to hold the InvoiceLogService instance
     *
     * @var InvoiceLogService
     */
    protected InvoiceLogService $logger;

    /**
     * Constructor for the controller
     *
     * @param InvoiceLogService $logger
     * An instance of the InvoiceLogService used for logging
     * invoice-related actions
     */
    public function __construct(InvoiceLogService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);

        return response()->json(
            Invoice::with('company', 'contact', 'items')->paginate($perPage)
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Invoice $invoice
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Invoice $invoice)
    {
        return response()->json(
            $invoice->load('company', 'contact', 'items')
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $this->validateInvoice($request);

        $invoice = Invoice::create($data);

        $this->logger->invoiceCreated(auth()->user(), auth()->id(), $invoice);

        return response()->json($invoice->load('items'), 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @param \App\Models\Invoice $invoice
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Invoice $invoice)
    {
        $data = $this->validateInvoice($request, $invoice);

        $invoice->update($data);

        $this->logger->invoiceUpdated(auth()->user(), auth()->id(), $invoice);

        return response()->json($invoice->fresh()->load('items'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Invoice $invoice
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        $this->logger->invoiceDeleted(
            auth()->user(),
            auth()->id(),
            $invoice
        );

        return response()->json(null, 204);
    }

    /**
     * Validate invoice payload for store/update.
     *
     * When $invoice is provided (update), the 'number' rule becomes
     * ['sometimes', 'required',
     * 'string', unique:invoices,number->ignore($invoice->id)]
     *
     * @param \Illuminate\Http\Request $request
     *
     * @param \App\Models\Invoice|null $invoice
     *
     * @return array
     */
    private function validateInvoice(
        Request $request,
        ?Invoice $invoice = null
    ): array {
        return $request->validate([
            'number' => $this->numberRule($invoice),
            'company_id' => 'nullable|integer|exists:companies,id',
            'contact_id' => 'nullable|integer|exists:contacts,id',
            'created_by' => 'nullable|integer|exists:users,id',
            'issue_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:draft,sent,paid,overdue,cancelled',
            'subtotal' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'total' => 'nullable|numeric',
            'currency' => 'nullable|string|max:8',
            'meta' => 'nullable|array',
        ]);
    }

    /**
     * Return validation rule for invoice number.
     *
     * @param \App\Models\Invoice|null $invoice
     *
     * @return array
     */
    private function numberRule(?Invoice $invoice = null): array
    {
        $uniqueRule = Rule::unique('invoices', 'number');
        if ($invoice) {
            $uniqueRule = $uniqueRule->ignore($invoice->id);
            return ['sometimes', 'required', 'string', $uniqueRule];
        }
        return ['required', 'string', $uniqueRule];
    }
}
