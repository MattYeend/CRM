<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Services\Invoices\InvoiceLogService;
use App\Services\Invoices\InvoiceManagementService;
use App\Services\Invoices\InvoiceQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the Invoice resource.
 *
 * Delegates business logic to three dedicated services:
 *   - InvoiceLogService — records audit log entries for invoice changes
 *   - InvoiceManagementService — handles create, update, delete, and restore
 *      operations
 *   - InvoiceQueryService — handles read/list queries with filtering and
 *      pagination
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class InvoiceController extends Controller
{
    /**
     * Service responsible for writing audit log entries for invoice events.
     *
     * @var InvoiceLogService
     */
    protected InvoiceLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * invoices.
     *
     * @var InvoiceManagementService
     */
    protected InvoiceManagementService $management;

    /**
     * Service responsible for querying and listing invoices.
     *
     * @var InvoiceQueryService
     */
    protected InvoiceQueryService $query;

    /**
     * Inject the required services into the controller.
     *
     * @param  InvoiceLogService $logger Handles audit logging for invoice
     * events.
     * @param  InvoiceManagementService $management Handles invoice
     * create/update/delete/restore.
     * @param  InvoiceQueryService $query Handles invoice listing and
     * retrieval.
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
     * Also includes the authenticated user's permissions for the Invoice
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated invoice data with pagination metadata and
     * permissions.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Invoice::class);

        $invoice = $this->query->list($request);

        return response()->json($invoice);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreInvoiceRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param  StoreInvoiceRequest $request Validated request containing
     * invoice data.
     *
     * @return JsonResponse The newly created invoice, with HTTP 201 Created.
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
     * Display the specified resource.
     *
     * Returns a single invoice by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  Invoice $invoice Route-model-bound invoice instance.
     *
     * @return JsonResponse The resolved invoice resource.
     */
    public function show(Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);

        $invoice = $this->query->show($invoice);

        return response()->json($invoice);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateInvoiceRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the
     * authenticated user.
     *
     * @param  UpdateInvoiceRequest $request Validated request containing
     * updated invoice data.
     * @param  Invoice $invoice Route-model-bound invoice instance to update.
     *
     * @return JsonResponse The updated invoice resource.
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
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * invoice instance is still fully accessible during logging.
     *
     * @param  Invoice $invoice Route-model-bound invoice instance to delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
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
     * Restore the specified invoice from soft deletion.
     *
     * Looks up the invoice including trashed records, then authorises via
     * the 'restore' policy. Returns 404 if the invoice is not currently
     * soft-deleted, preventing accidental double-restores.
     *
     * @param  int|string $id The primary key of the soft-deleted invoice.
     *
     * @return JsonResponse The restored invoice resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the invoice is not trashed (404).
     */
    public function restore($id): JsonResponse
    {
        $invoice = Invoice::withTrashed()->findOrFail($id);
        $this->authorize('restore', $invoice);

        if (! $invoice->trashed()) {
            abort(404);
        }

        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->invoiceRestored(
            $user,
            $user->id,
            $invoice,
        );

        return response()->json($invoice);
    }
}
