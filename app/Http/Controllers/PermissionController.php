<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Models\Permission;
use App\Services\Permissions\PermissionLogService;
use App\Services\Permissions\PermissionManagementService;
use App\Services\Permissions\PermissionQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the Permission resource.
 *
 * Delegates business logic to three dedicated services:
 *   - PermissionLogService — records audit log entries
 *   - PermissionManagementService — handles create, update, delete, restore
 *   - PermissionQueryService — handles retrieval and listing
 *
 * All responses are returned as JSON for API consumption.
 */
class PermissionController extends Controller
{
    /**
     * Service responsible for writing audit log entries for permission events.
     *
     * @var PermissionLogService
     */
    protected PermissionLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * permissions.
     *
     * @var PermissionManagementService
     */
    protected PermissionManagementService $management;

    /**
     * Service responsible for querying and listing permissions.
     *
     * @var PermissionQueryService
     */
    protected PermissionQueryService $query;

    /**
     * Inject required services into the controller.
     *
     * @param  PermissionLogService $logger Handles audit logging permissions.
     *
     * @param  PermissionManagementService $management Handles creation
     * and management of permissionss.
     *
     * @param  PermissionQueryService $query Handles retrieval and listing
     * of permissions.
     */
    public function __construct(
        PermissionLogService $logger,
        PermissionManagementService $management,
        PermissionQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
    }

    /**
     * Display a listing of stock movements for a given permission.
     *
     * Also includes the authenticated user's permissions for the
     * Permission resource, so the frontend can conditionally
     * render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may include filters or
     * pagination parameters.
     *
     * @return JsonResponse Paginated permissions data with pagination metadata
     * and permissions.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Permission::class);

        $permission = $this->query->list($request);

        return response()->json($permission);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePermissionRequest $request
     *
     * @return JsonResponse
     * Validation is handled upstream by StorePermissionRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     *
     * @param StorePermissionRequest $request Validated request containing the
     * permission data.
     *
     * @return JsonResponse The newly created permission, HTTP 201 Created.
     */
    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = $this->management->store($request);

        $user = $request->user();

        $this->logger->permissionCreated(
            $user,
            $user->id,
            $permission,
        );

        return response()->json($permission, 201);
    }

    /**
     * Display the specified resource.
     *
     * Returns a single permission by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  Permission $permission Route-model-bound permission instance.
     *
     * @return JsonResponse The resolved permission resource.
     */
    public function show(Permission $permission): JsonResponse
    {
        $this->authorize('view', $permission);

        $permission = $this->query->show($permission);

        return response()->json($permission);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdatePermissionRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the
     * authenticated user.
     *
     * @param  UpdatePermissionRequest $request Validated request containing
     * updated permission data.
     *
     * @param  Permission $permission Route-model-bound permission instance
     * to update.
     *
     * @return JsonResponse The updated permission resource.
     */
    public function update(
        UpdatePermissionRequest $request,
        Permission $permission
    ): JsonResponse {
        $permission = $this->management->update($request, $permission);

        $user = $request->user();

        $this->logger->permissionUpdated(
            $user,
            $user->id,
            $permission,
        );

        return response()->json($permission);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * permission instance is still fully accessible during logging.
     *
     * @param  Permission $permission Route-model-bound permission
     * instance to delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
     */
    public function destroy(Permission $permission): JsonResponse
    {
        $this->authorize('delete', $permission);

        $user = auth()->user();

        $this->logger->permissionDeleted(
            $user,
            $user->id,
            $permission,
        );

        $this->management->destroy($permission);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified permission from soft deletion.
     *
     * Looks up the permission including trashed records, then authorises via
     * the 'restore' policy. Returns 404 if the permission is not currently
     * soft-deleted, preventing accidental double-restores.
     *
     * @param  int|string $id The primary key of the soft-deleted permission.
     *
     * @return JsonResponse The restored permission resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the permission is not trashed (404).
     */
    public function restore(int $id): JsonResponse
    {
        $permission = Permission::withTrashed()->findOrFail($id);
        $this->authorize('restore', $permission);

        if (! $permission->trashed()) {
            abort(404);
        }

        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->permissionRestored(
            $user,
            $user->id,
            $permission
        );

        return response()->json($permission);
    }
}
