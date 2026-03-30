<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\Suppliers\SupplierLogService;
use App\Services\Suppliers\SupplierManagementService;
use App\Services\Suppliers\SupplierQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the Supplier resource.
 *
 * Delegates business logic to three dedicated services:
 *   - SupplierLogService — records audit log entries for supplier changes
 *   - SupplierManagementService — handles create, update, delete, and restore
 *      operations
 *   - SupplierQueryService — handles read/list queries with filtering and
 *      pagination
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class SupplierController extends Controller
{
    /**
     * Service responsible for writing audit log entries for supplier events.
     *
     * @var SupplierLogService
     */
    protected SupplierLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * suppliers.
     *
     * @var SupplierManagementService
     */
    protected SupplierManagementService $management;

    /**
     * Service responsible for querying and listing suppliers.
     *
     * @var SupplierQueryService
     */
    protected SupplierQueryService $query;

    /**
     * Inject the required services into the controller.
     *
     * @param  SupplierLogService $logger Handles audit logging for
     * supplier events.
     *
     * @param  SupplierManagementService $management Handles supplier
     * create/update/delete/restore.
     *
     * @param  SupplierQueryService $query Handles supplier listing and
     * retrieval.
     */
    public function __construct(
        SupplierLogService $logger,
        SupplierManagementService $management,
        SupplierQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
    }

    /**
     * Display a listing of the resource.
     *
     * Also includes the authenticated user's permissions for the Supplier
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated supplier data with pagination metadata and
     * permissions.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Supplier::class);

        $part = $this->query->list($request);

        return response()->json($part);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreSupplierRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param  StoreSupplierRequest $request Validated request containing
     * supplier data.
     *
     * @return JsonResponse The newly created supplier, with HTTP 201 Created.
     */
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = $this->management->store($request);

        $user = $request->user();

        $this->logger->supplierCreated(
            $user,
            $user->id,
            $supplier,
        );

        return response()->json($supplier, 201);
    }

    /**
     * Display the specified resource.
     *
     * Returns a single supplier by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  Supplier $supplier Route-model-bound supplier instance.
     *
     * @return JsonResponse The resolved supplier resource.
     */
    public function show(Supplier $supplier): JsonResponse
    {
        $this->authorize('view', $supplier);

        $supplier = $this->query->show($supplier);

        return response()->json($supplier);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateSupplierRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the
     * authenticated user.
     *
     * @param  UpdateSupplierRequest $request Validated request containing
     * updated supplier data.
     *
     * @param  Supplier $supplier Route-model-bound supplier instance to update.
     *
     * @return JsonResponse The updated supplier resource.
     */
    public function update(
        UpdateSupplierRequest $request,
        Supplier $supplier
    ): JsonResponse {
        $supplier = $this->management->update($request, $supplier);

        $user = $request->user();

        $this->logger->supplierUpdated(
            $user,
            $user->id,
            $supplier,
        );

        return response()->json($supplier);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * supplier instance is still fully accessible during logging.
     *
     * @param  Supplier $supplier Route-model-bound supplier instance to delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
     */
    public function destroy(Supplier $supplier): JsonResponse
    {
        $this->authorize('delete', $supplier);

        $user = auth()->user();

        $this->logger->supplierDeleted(
            $user,
            $user->id,
            $supplier,
        );

        $this->management->destroy($supplier);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified supplier from soft deletion.
     *
     * Looks up the supplier including trashed records, then authorises via
     * the 'restore' policy. Returns 404 if the supplier is not currently
     * soft-deleted, preventing accidental double-restores.
     *
     * @param  int|string $id The primary key of the soft-deleted supplier.
     *
     * @return JsonResponse The restored supplier resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the supplier is not trashed (404).
     */
    public function restore(int $id): JsonResponse
    {
        $supplier = Supplier::withTrashed()->findOrFail($id);

        $this->authorize('restore', $supplier);

        if (! $supplier->trashed()) {
            abort(404);
        }

        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->supplierRestored(
            $user,
            $user->id,
            $supplier
        );

        return response()->json($supplier);
    }
}
