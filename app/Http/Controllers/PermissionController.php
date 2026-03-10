<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Models\Permission;
use App\Services\PermissionLogService;
use App\Services\PermissionManagementService;
use App\Services\PermissionQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Declare a protected property to hold the PermissionLogService,
     * PermissionManagementService and PermissionQueryService instance
     *
     * @var PermissionLogService
     * @var PermissionManagementService
     * @var PermissionQueryServic
     */
    protected PermissionLogService $logger;
    protected PermissionManagementService $managementService;
    protected PermissionQueryService $queryService;

    /**
     * Constructor for the controller
     *
     * @param PermissionLogService $logger
     *
     * @param PermissionManagementService $management
     *
     * @param PermissionQueryService $query
     *
     * An instance of the PermissionLogService used for logging
     * permission-related actions
     * An instance of the PermissionManagementService for management
     * of permissions
     * An instance of the PermissionQueryService for the query of
     * permission-related actions
     */
    public function __construct(
        PermissionLogService $logger,
        PermissionManagementService $managementService,
        PermissionQueryService $queryService,
    ) {
        $this->logger = $logger;
        $this->managementService = $managementService;
        $this->queryService = $queryService;
    }

    /**
     * Display a listing of the permissions with their roles.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Permission::class);

        $permission = $this->queryService->list($request);

        return response()->json($permission);
    }

    /**
     * Display the specified permission with its roles.
     *
     * @param Permission $permission
     *
     * @return JsonResponse
     */
    public function show(Permission $permission): JsonResponse
    {
        $this->authorize('view', $permission);

        $permission = $this->queryService->show($permission);

        return response()->json($permission);
    }

    /**
     * Store a newly created permission in storage.
     *
     * @param StorePermissionRequest $request
     *
     * @return JsonResponse
     */
    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = $this->managementService->store($request);

        $user = $request->user();

        $this->logger->permissionCreated(
            $user,
            $user->id,
            $permission,
        );

        return response()->json($permission, 201);
    }

    /**
     * Update the specified permission in storage.
     *
     * @param UpdatePermissionRequest $request
     *
     * @param Permission $permission
     *
     * @return JsonResponse
     */
    public function update(
        UpdatePermissionRequest $request,
        Permission $permission
    ): JsonResponse {
        $permission = $this->managementService->update($request, $permission);

        $user = $request->user();

        $this->logger->permissionUpdated(
            $user,
            $user->id,
            $permission,
        );

        return response()->json($permission);
    }

    /**
     * Remove the specified permission from storage.
     *
     * @param Permission $permission
     *
     * @return JsonResponse
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

        $permission = $this->managementService->destroy($permission);

        return response()->json(null, 204);
    }
}
