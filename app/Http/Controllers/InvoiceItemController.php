<?php

namespace App\Http\Controllers;

use App\Models\InvoiceItem;
use Illuminate\Http\Request;

class InvoiceItemController extends Controller
{
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
    public function show(InvoiceItem $invoiceItem)
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
    public function store(Request $request)
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

        $data['line_total'] = $data['line_total'] ?? ($data['quantity'] * $data['unit_price']);

        $item = InvoiceItem::create($data);
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
    public function update(Request $request, InvoiceItem $invoiceItem)
    {
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
        return response()->json($invoiceItem->fresh()->load('product'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\InvoiceItem $invoiceItem
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(InvoiceItem $invoiceItem)
    {
        $invoiceItem->delete();
        return response()->json(null, 204);
    }
}
