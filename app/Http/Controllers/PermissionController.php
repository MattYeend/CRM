<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Services\PermissionLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Permission $permission): JsonResponse
    {
        return response()->json($permission->load('roles'));
    }

    /**
     * Store a newly created permission in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|unique:permissions,name',
            'label' => 'nullable|string',
        ]);

        $permission = Permission::create($data);

        $this->logger->permissionCreated(
            $request->user(),
            $request->user()->id,
            $permission
        );

        return response()->json($permission, 201);
    }

    /**
     * Update the specified permission in storage.
     *
     * @param Request $request
     *
     * @param Permission $permission
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(
        Request $request,
        Permission $permission
    ): JsonResponse {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                Rule::unique('permissions', 'name')->ignore($permission->id),
            ],
            'label' => 'nullable|string',
        ]);

        $permission->update($data);

        $this->logger->permissionUpdated(
            $request->user(),
            $request->user()->id,
            $permission
        );

        return response()->json($permission);
    }

    /**
     * Remove the specified permission from storage.
     *
     * @param Permission $permission
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Permission $permission): JsonResponse
    {
        $this->logger->permissionDeleted(
            auth()->user(),
            auth()->user()->id,
            $permission
        );

        $permission->roles()->detach();
        $permission->delete();

        return response()->json(null, 204);
    }
}
