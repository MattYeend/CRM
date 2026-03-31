<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartSerialNumberRequest;
use App\Http\Requests\UpdatePartSerialNumberRequest;
use App\Models\Part;
use App\Models\PartSerialNumber;
use App\Services\PartSerialNumbers\PartSerialNumberLogService;
use App\Services\PartSerialNumbers\PartSerialNumberManagementService;
use App\Services\PartSerialNumbers\PartSerialNumberQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the PartSerialNumber resource.
 *
 * Delegates business logic to three dedicated services:
 *   - PartSerialNumberLogService — records audit log entries for part serial
 *      number changes
 *   - PartSerialNumberManagementService — handles create, update, delete,
 *      and restore operations
 *   - PartSerialNumberQueryService — handles read/list queries with filtering
 *      ans pagination
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class PartSerialNumberController extends Controller
{
    /**
     * Service responsible for writing audit log entries for part serial number
     * events.
     *
     * @var PartSerialNumberLogService
     */
    protected PartSerialNumberLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * part serial numbers.
     *
     * @var PartSerialNumberManagementService
     */
    protected PartSerialNumberManagementService $management;

    /**
     * Service responsible for querying and listing part serial numbers.
     *
     * @var PartSerialNumberQueryService
     */
    protected PartSerialNumberQueryService $query;

    /**
     * Inject the required services into the controller.
     *
     * @param  PartSerialNumberLogService $logger Handles audit logging for part
     * serial number events.
     * @param  PartSerialNumberManagementService $management Handles part serial
     * number create/update/delete/restore.
     * @param  PartSerialNumberQueryService $query Handles part serial number
     * listing and retrieval.
     */
    public function __construct(
        PartSerialNumberLogService $logger,
        PartSerialNumberManagementService $management,
        PartSerialNumberQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
    }

    /**
     * Display a paginated listing of the resource.
     *
     * Also includes the authenticated user's permissions for the
     * PartSerialNumber resource, so the frontend can conditionally
     * render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated part serial number data with pagination
     * metadata and permissions.
     */
    public function index(Request $request, Part $part): JsonResponse
    {
        $this->authorize('viewAny', PartSerialNumber::class);

        $serials = $this->query->list($request, $part);

        return response()->json($serials);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StorePartSerialNumberRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param StorePartSerialNumberRequest $request
     * @param Part $part
     *
     * @return JsonResponse
     */
    public function store(
        StorePartSerialNumberRequest $request,
        Part $part
    ): JsonResponse {
        $serial = $this->management->store($request, $part);

        $user = $request->user();

        $this->logger->partSerialNumberCreated(
            $user,
            $user->id,
            $serial,
        );

        return response()->json($serial, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdatePartSerialNumberRequest,
     * which also implicitly authorises the operation via its
     * authorize() method.
     *
     * After updating, an audit log entry is written against the
     * authenticated user.
     *
     * @param  UpdatePartSerialNumberRequest $request Validated request
     * containing updated part serial number data.
     * @param  PartSerialNumber $partserialnumber Route-model-bound part serial
     * number instance to update.
     *
     * @return JsonResponse The updated part serial number resource.
     */
    public function update(
        UpdatePartSerialNumberRequest $request,
        Part $part,
        PartSerialNumber $serialNumber
    ): JsonResponse {
        $serialNumber = $this->management->update($request, $serialNumber);

        $user = $request->user();

        $this->logger->partSerialNumberUpdated(
            $user,
            $user->id,
            $serialNumber,
        );

        return response()->json($serialNumber);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * part serial number instance is still fully accessible during logging.
     *
     * @param  PartSerialNumber $partserialnumber Route-model-bound part
     * serial number instance to delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
     */
    public function destroy(
        Part $part,
        PartSerialNumber $serialNumber
    ): JsonResponse {
        $this->authorize('delete', $serialNumber);

        $user = auth()->user();

        $this->logger->partSerialNumberDeleted(
            $user,
            $user->id,
            $serialNumber,
        );

        $this->management->destroy($serialNumber);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified resource from storage.
     *
     * Looks up the part serial number including trashed records, then
     * authorises via the 'restore' policy. Returns 404 if the part
     * serial number is not currently soft-deleted, preventing
     * accidental double-restores.
     *
     * @param  int|string $id The primary key of the soft-deleted
     * part serial number.
     *
     * @return JsonResponse The restored part serial number resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the part serial number is not trashed (404).
     */
    public function restore($id): JsonResponse
    {
        $serialNumber = PartSerialNumber::withTrashed()->findOrFail($id);
        $this->authorize('restore', $serialNumber);

        if (! $serialNumber->trashed()) {
            abort(404);
        }

        $this->management->restore($id);

        $user = auth()->user();

        $this->logger->partSerialNumberRestored(
            $user,
            $user->id,
            $serialNumber,
        );

        return response()->json($serialNumber);
    }
}
