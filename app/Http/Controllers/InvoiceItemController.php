<?php

namespace App\Http\Controllers;

use App\Models\InvoiceItem;
use App\Services\InvoiceItemLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceItemController extends Controller
{
    /**
     * Declare a protected property to hold the InvoiceItemLogService instance
     *
     * @var InvoiceItemLogService
     */
    protected InvoiceItemLogService $logger;

    /**
     * Constructor for the controller
     *
     * @param InvoiceItemLogService $logger
     * An instance of the InvoiceItemLogService used for logging
     * invoice item-related actions
     */
    public function __construct(InvoiceItemLogService $logger)
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
    public function index(Request $request): JsonResponse
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        return response()->json(
            InvoiceItem::with('invoice', 'product')->paginate($perPage)
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\InvoiceItem $invoiceItem
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(InvoiceItem $invoiceItem): JsonResponse
    {
        return response()->json($invoiceItem->load('invoice', 'product'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'invoice_id' => 'required|integer|exists:invoices,id',
            'product_id' => 'nullable|integer|exists:products,id',
            'description' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric',
            'line_total' => 'nullable|numeric',
            'meta' => 'nullable|array',
        ]);

        $data['line_total'] = $data['line_total']
            ?? $data['quantity'] * $data['unit_price'];

        $item = InvoiceItem::create($data);

        $this->logger->invoiceItemCreated(
            auth()->user(),
            auth()->id(),
            $item
        );

        return response()->json($item->load('product'), 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @param \App\Models\InvoiceItem $invoiceItem
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(
        Request $request,
        InvoiceItem $invoiceItem
    ): JsonResponse {
        $data = $request->validate([
            'description' => 'sometimes|required|string',
            'quantity' => 'nullable|integer|min:1',
            'unit_price' => 'nullable|numeric',
            'line_total' => 'nullable|numeric',
            'meta' => 'nullable|array',
        ]);

        if (isset($data['quantity']) && isset($data['unit_price'])) {
            $data['line_total'] = $data['quantity'] * $data['unit_price'];
        }

        $invoiceItem->update($data);

        $this->logger->invoiceItemUpdated(
            auth()->user(),
            auth()->id(),
            $invoiceItem
        );

        return response()->json($invoiceItem->fresh()->load('product'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\InvoiceItem $invoiceItem
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(InvoiceItem $invoiceItem): JsonResponse
    {
        $this->logger->invoiceItemDeleted(
            auth()->user(),
            auth()->id(),
            $invoiceItem
        );

        $invoiceItem->delete();

        return response()->json(null, 204);
    }
}
