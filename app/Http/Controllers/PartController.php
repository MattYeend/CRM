<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartRequest;
use App\Http\Requests\UpdatePartRequest;
use App\Models\Part;
use App\Services\Parts\PartLogService;
use App\Services\Parts\PartManagementService;
use App\Services\Parts\PartQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the Part resource.
 *
 * Delegates business logic to three dedicated services:
 *   - PartLogService — records audit log entries for part changes
 *   - PartManagementService — handles create, update, delete, and restore
 *      operations
 *   - PartQueryService — handles read/list queries with filtering and
 *      pagination
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class PartController extends Controller
{
    /**
     * Service responsible for writing audit log entries for part events.
     *
     * @var PartLogService
     */
    protected PartLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * parts.
     *
     * @var PartManagementService
     */
    protected PartManagementService $management;

    /**
     * Service responsible for querying and listing parts.
     *
     * @var PartQueryService
     */
    protected PartQueryService $query;

    /**
     * Inject the required services into the controller.
     *
     * @param  PartLogService $logger Handles audit logging for part events.
     * @param  PartManagementService $management Handles part
     * create/update/delete/restore.
     * @param  PartQueryService $query Handles part listing and retrieval.
     */
    public function __construct(
        PartLogService $logger,
        PartManagementService $management,
        PartQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
    }

    /**
     * Display a listing of the resource.
     *
     * Also includes the authenticated user's permissions for the Part
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated part data with pagination metadata and
     * permissions.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Part::class);

        $parts = $this->query->list($request);

        return response()->json($parts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StorePartRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param  StorePartRequest $request Validated request containing part data.
     *
     * @return JsonResponse The newly created part, with HTTP 201 Created.
     */
    public function store(StorePartRequest $request): JsonResponse
    {
        $part = $this->management->store($request);

        $user = $request->user();

        $this->logger->partCreated(
            $user,
            $user->id,
            $part,
        );

        return response()->json($part, 201);
    }

    /**
     * Display the specified resource.
     *
     * Returns a single part by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  Part $part Route-model-bound part instance.
     *
     * @return JsonResponse The resolved part resource.
     */
    public function show(Part $part): JsonResponse
    {
        $this->authorize('view', $part);
        $this->authorize('access', $part);

        $part = $this->query->show($part);

        return response()->json($part);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdatePartRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the
     * authenticated user.
     *
     * @param  UpdatePartRequest $request Validated request containing updated
     * part data.
     * @param  Part $part Route-model-bound part instance to update.
     *
     * @return JsonResponse The updated part resource.
     */
    public function update(
        UpdatePartRequest $request,
        Part $part
    ): JsonResponse {
        $part = $this->management->update($request, $part);

        $user = $request->user();

        $this->logger->partUpdated(
            $user,
            $user->id,
            $part,
        );

        return response()->json($part);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * part instance is still fully accessible during logging.
     *
     * @param  Part $part Route-model-bound part instance to delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
     */
    public function destroy(Part $part): JsonResponse
    {
        $this->authorize('delete', $part);

        $user = auth()->user();

        $this->logger->partDeleted(
            $user,
            $user->id,
            $part,
        );

        $this->management->destroy($part);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified part from soft deletion.
     *
     * Looks up the part including trashed records, then authorises via
     * the 'restore' policy. Returns 404 if the part is not currently
     * soft-deleted, preventing accidental double-restores.
     *
     * @param  int|string $id The primary key of the soft-deleted part.
     *
     * @return JsonResponse The restored part resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the part is not trashed (404).
     */
    public function restore(int $id): JsonResponse
    {
        $part = Part::withTrashed()->findOrFail($id);
        $this->authorize('restore', $part);

        if (! $part->trashed()) {
            abort(404);
        }

        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->partRestored(
            $user,
            $user->id,
            $part
        );

        return response()->json($part);
    }
}
