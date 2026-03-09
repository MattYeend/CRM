<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Services\InvoiceLogService;
use App\Services\InvoiceManagementService;
use App\Services\InvoiceQueryService;
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
    protected InvoiceManagementService $managementService;
    protected InvoiceQueryService $queryService;

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
        InvoiceManagementService $managementService,
        InvoiceQueryService $queryService,
    ) {
        $this->logger = $logger;
        $this->managementService = $managementService;
        $this->queryService = $queryService;
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

        $invoice = $this->queryService->list($request);

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

        $invoice = $this->queryService->show($invoice);

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
        $invoice = $this->managementService->store($request);

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
        $invoice = $this->managementService->update($request, $invoice);

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

        $this->managementService->destroy($invoice);

        return response()->json(null, 204);
    }
}
