<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceItemRequest;
use App\Http\Requests\UpdateInvoiceItemRequest;
use App\Models\InvoiceItem;
use App\Services\InvoiceItems\InvoiceItemLogService;
use App\Services\InvoiceItems\InvoiceItemManagementService;
use App\Services\InvoiceItems\InvoiceItemQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the InvoiceItem resource.
 *
 * Delegates business logic to three dedicated services:
 *   - InvoiceItemLogService — records audit log entries for invoice item
 *      changes
 *   - InvoiceItemManagementService — handles create, update, delete, and
 *      restore operations
 *   - InvoiceItemQueryService — handles read/list queries with filtering and
 *      pagination
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class InvoiceItemController extends Controller
{
    /**
     * Service responsible for writing audit log entries for invoice item
     * events.
     *
     * @var InvoiceItemLogService
     */
    protected InvoiceItemLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * invoice items.
     *
     * @var InvoiceItemManagementService
     */
    protected InvoiceItemManagementService $management;

    /**
     * Service responsible for querying and listing invoice items.
     *
     * @var InvoiceItemQueryService
     */
    protected InvoiceItemQueryService $query;

    /**
     * Inject the required services into the controller.
     *
     * @param  InvoiceItemLogService $logger Handles audit logging for invoice
     * item events.
     *
     * @param  InvoiceItemManagementService $management Handles invoice item
     * create/update/delete/restore.
     *
     * @param  InvoiceItemQueryService $query Handles invoice item listing and
     * retrieval.
     */
    public function __construct(
        InvoiceItemLogService $logger,
        InvoiceItemManagementService $management,
        InvoiceItemQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
    }

    /**
     * Display a listing of the resource.
     *
     * Also includes the authenticated user's permissions for the Invoice Item
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated invoice item data.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', InvoiceItem::class);

        $invoiceItem = $this->query->list($request);

        return response()->json($invoiceItem);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreInvoiceItemRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param  StoreInvoiceItemRequest $request Validated request containing
     * invoice item data.
     *
     * @return JsonResponse The newly created invoice item, with HTTP 201
     * Created.
     */
    public function store(StoreInvoiceItemRequest $request): JsonResponse
    {
        $invoiceItem = $this->management->store($request);

        $user = $request->user();

        $this->logger->invoiceItemCreated(
            $user,
            $user->id,
            $invoiceItem,
        );

        return response()->json($invoiceItem->load('product'), 201);
    }

    /**
     * Display the specified resource.
     *
     * Returns a single invoice item by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  InvoiceItem $invoiceItem Route-model-bound invoice item instance.
     *
     * @return JsonResponse The resolved invoice item resource.
     */
    public function show(InvoiceItem $invoiceItem): JsonResponse
    {
        $this->authorize('view', $invoiceItem);

        $invoiceItem = $this->query->show($invoiceItem);

        return response()->json($invoiceItem);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateInvoiceItemRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the
     * authenticated user.
     *
     * @param  UpdateInvoiceItemRequest $request Validated request containing
     * updated invoice item data.
     *
     * @param  InvoiceItem $invoiceItem Route-model-bound invoice item instance
     * to update.
     *
     * @return JsonResponse The updated invoice item resource.
     */
    public function update(
        UpdateInvoiceItemRequest $request,
        InvoiceItem $invoiceItem
    ): JsonResponse {
        $invoiceItem = $this->management->update($request, $invoiceItem);

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
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * invoice item instance is still fully accessible during logging.
     *
     * @param  InvoiceItem $invoiceItem Route-model-bound invoice item instance
     * to delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
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

        $this->management->destroy($invoiceItem);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified invoice item from soft deletion.
     *
     * Looks up the invoice item including trashed records, then authorises via
     * the 'restore' policy. Returns 404 if the invoice item is not currently
     * soft-deleted, preventing accidental double-restores.
     *
     * @param  int|string $id The primary key of the soft-deleted invoice item.
     *
     * @return JsonResponse The restored invoice item resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the invoice item is not trashed (404).
     */
    public function restore($id): JsonResponse
    {
        $invoiceItem = InvoiceItem::withTrashed()->findOrFail($id);
        $this->authorize('restore', $invoiceItem);

        if (! $invoiceItem->trashed()) {
            abort(404);
        }

        $this->management->restore($id);

        $user = auth()->user();

        $this->logger->invoiceItemRestored(
            $user,
            $user->id,
            $invoiceItem,
        );

        return response()->json($invoiceItem);
    }
}
