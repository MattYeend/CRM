<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceItemRequest;
use App\Http\Requests\UpdateInvoiceItemRequest;
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
     *
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
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', InvoiceItem::class);

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
     * @param InvoiceItem $invoiceItem
     *
     * @return JsonResponse
     */
    public function show(InvoiceItem $invoiceItem): JsonResponse
    {
        $this->authorize('view', $invoiceItem);

        return response()->json($invoiceItem->load('invoice', 'product'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreInvoiceItemRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreInvoiceItemRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $data['created_by'] = $user->id;

        $data['line_total'] = $data['line_total']
            ?? $data['quantity'] * $data['unit_price'];

        $item = InvoiceItem::create($data);

        $this->logger->invoiceItemCreated(
            $user,
            $user->id,
            $item
        );

        return response()->json($item->load('product'), 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     *
     * @param InvoiceItem $invoiceItem
     *
     * @return JsonResponse
     */
    public function update(
        UpdateInvoiceItemRequest $request,
        InvoiceItem $invoiceItem
    ): JsonResponse {
        $user = $request->user();
        $data = $request->validated();
        $data['updated_by'] = $user->id;

        if (isset($data['quantity']) && isset($data['unit_price'])) {
            $data['line_total'] = $data['quantity'] * $data['unit_price'];
        }

        $invoiceItem->update($data);

        $this->logger->invoiceItemUpdated(
            $user,
            $user->id,
            $invoiceItem
        );

        return response()->json($invoiceItem->fresh()->load('product'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param InvoiceItem $invoiceItem
     *
     * @return JsonResponse
     */
    public function destroy(InvoiceItem $invoiceItem): JsonResponse
    {
        $this->authorize('delete', $invoiceItem);

        $user = auth()->user();

        $this->logger->invoiceItemDeleted(
            $user,
            $user->id,
            $invoiceItem,
        );

        $invoiceItem->update([
            'deleted_by' => $user->id,
        ]);
        $invoiceItem->delete();

        return response()->json(null, 204);
    }
}
