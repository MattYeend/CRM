<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Models\Permission;
use App\Services\PermissionLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Declare a protected property to hold the PermissionLogService instance
     *
     * @var PermissionLogService
     */
    protected PermissionLogService $logger;

    /**
     * Constructor for the controller
     *
     * @param PermissionLogService $logger
     *
     * An instance of the PermissionLogService used for logging
     * permission-related actions
     */
    public function __construct(PermissionLogService $logger)
    {
        $this->logger = $logger;
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

        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        return response()->json(
            Permission::with('roles')
                ->paginate($perPage)
        );
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

        return response()->json($permission->load('roles'));
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
        $user = $request->user();
        $data = $request->validated();
        $data['created_by'] = $user->id;

        $permission = Permission::create($data);

        $this->logger->permissionCreated(
            $user,
            $user->id,
            $permission
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
        $user = $request->user();
        $data = $request->validated();
        $data['updated_by'] = $user->id;

        $permission->update($data);

        $this->logger->permissionUpdated(
            $user,
            $user->id,
            $permission
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

        $permission->roles()->detach();
        $permission->save();
        $permission->delete();

        return response()->json(null, 204);
    }
}
