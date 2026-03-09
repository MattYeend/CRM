<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceItemRequest;
use App\Http\Requests\UpdateInvoiceItemRequest;
use App\Models\InvoiceItem;
use App\Services\InvoiceItemLogService;
use App\Services\InvoiceItemManagementService;
use App\Services\InvoiceItemQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceItemController extends Controller
{
    /**
     * Declare a protected property to hold the InvoiceItemLogService,
     * InvoiceItemManagementService and InvoiceItemQueryService instance
     *
     * @var InvoiceItemLogService
     * @var InvoiceManagementService
     * @var InvoiceQueryService
     */
    protected InvoiceItemLogService $logger;
    protected InvoiceItemManagementService $managementService;
    protected InvoiceItemQueryService $queryService;

    /**
     * Constructor for the controller
     *
     * @param InvoiceItemLogService $logger
     *
     * @param InvoiceItemManagementService $management
     *
     * @param InvoiceItemQueryService $query
     *
     * An instance of the InvoiceItemLogService used for logging
     * invoice item-related actions
     * An instance of the InvoiceItemManagementService for management
     * of invoice items
     * An instance of the InvoiceItemQueryService for the query of
     * invoice items-related actions
     */
    public function __construct(
        InvoiceItemLogService $logger,
        InvoiceItemManagementService $managementService,
        InvoiceItemQueryService $queryService,
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
        $this->authorize('viewAny', InvoiceItem::class);

        $invoiceItem = $this->queryService->list($request);

        return response()->json($invoiceItem);
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

        $invoiceItem = $this->queryService->show($invoiceItem);

        return response()->json($invoiceItem);
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
        $invoiceItem = $this->managementService->store($request);

        $user = $request->user();

        $this->logger->invoiceItemCreated(
            $user,
            $user->id,
            $invoiceItem,
        );

        return response()->json($invoiceItem->load('product'), 201);
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
        $invoiceItem = $this->managementService->update($request, $invoiceItem);

        $user = $request->user();

        $this->logger->invoiceItemUpdated(
            $user,
            $user->id,
            $invoiceItem,
        );

        return response()->json($invoiceItem);
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

        $invoiceItem = $this->managementService->destroy($invoiceItem);

        return response()->json(null, 204);
    }
}
