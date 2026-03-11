<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Services\Invoices\InvoiceLogService;
use App\Services\Invoices\InvoiceManagementService;
use App\Services\Invoices\InvoiceQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Declare a protected property to hold the InvoiceLogService,
     * InvoiceManagementService and InvoiceQueryService instance
     *
     * @var InvoiceLogService
     * @var InvoiceManagementService
     * @var InvoiceQueryService
     */
    protected InvoiceLogService $logger;
    protected InvoiceManagementService $management;
    protected InvoiceQueryService $query;

    /**
     * Constructor for the controller
     *
     * @param InvoiceLogService $logger
     *
     * @param InvoiceManagementService $management
     *
     * @param InvoiceQueryService $query
     *
     * An instance of the InvoiceLogService used for logging
     * invoice-related actions
     * An instance of the InvoiceManagementService for management
     * of invoices
     * An instance of the InvoiceQueryService for the query of
     * invoice-related actions
     */
    public function __construct(
        InvoiceLogService $logger,
        InvoiceManagementService $management,
        InvoiceQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
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
        $this->authorize('viewAny', Invoice::class);

        $invoice = $this->query->list($request);

        return response()->json($invoice);
    }

    /**
     * Display the specified resource.
     *
     * @param Invoice $invoice
     *
     * @return JsonResponse
     */
    public function show(Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);

        $invoice = $this->query->show($invoice);

        return response()->json($invoice);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreInvoiceRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->management->store($request);

        $user = $request->user();

        $this->logger->invoiceCreated(
            $user,
            $user->id,
            $invoice,
        );

        return response()->json($invoice, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateInvoiceRequest $request
     *
     * @param Invoice $invoice
     *
     * @return JsonResponse
     */
    public function update(
        UpdateInvoiceRequest $request,
        Invoice $invoice
    ): JsonResponse {
        $invoice = $this->management->update($request, $invoice);

        $user = $request->user();

        $this->logger->invoiceUpdated(
            $user,
            $user->id,
            $invoice,
        );

        return response()->json($invoice->fresh()->load('items'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Invoice $invoice
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        $this->authorize('delete', $invoice);

        $user = auth()->user();

        $this->logger->invoiceDeleted(
            $user,
            $user->id,
            $invoice,
        );

        $this->management->destroy($invoice);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function restore($id): JsonResponse
    {
        $invoice = $this->management->restore((int) $id);

        $this->authorize('restore', $invoice);

        $user = auth()->user();

        $this->logger->invoiceRestored(
            $user,
            $user->id,
            $invoice,
        );

        return response()->json($invoice);
    }
}
