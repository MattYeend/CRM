<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\RoleLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Declare a protected property to hold the RoleLogService instance
     *
     * @var RoleLogService
     */
    protected RoleLogService $logger;

    /**
     * Constructor for the controller
     *
     * @param RoleLogService $logger
     * An instance of the RoleLogService used for logging
     * role-related actions
     */
    public function __construct(RoleLogService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Display a listing of the roles with user counts and permissions.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        return response()->json(
            Role::withCount('users')
                ->with('permissions')
                ->paginate($perPage)
        );
    }

    /**
     * Display the specified role with its permissions and users.
     *
     * @param Role $role
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Role $role)
    {
        return response()->json($role->load('permissions', 'users'));
    }

    /**
     * Store a newly created role in storage with optional permissions.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'label' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $role = Role::create($request->only(['name', 'label']));

        if (isset($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }

        $this->logger->roleCreated(
            $request->user(),
            $request->user()->id,
            $role
        );

        return response()->json($role->load('permissions'), 201);
    }

    /**
     * Update the specified role in storage with optional permissions.
     *
     * @param Request $request
     *
     * @param Role $role
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Role $role)
    {
        $data = $this->validateRoleData($request, $role);

        $role->update($request->only(['name', 'label']));

        $this->syncPermissions($role, $data);

        $this->logger->roleUpdated(
            $request->user(),
            $request->user()->id,
            $role
        );

        return response()->json($role->load('permissions'));
    }

    /**
     * Remove the specified role from storage.
     *
     * @param Role $role
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Role $role)
    {
        $this->logger->roleDeleted(
            auth()->user(),
            auth()->user()->id,
            $role
        );

        $role->permissions()->detach();
        $role->users()->detach();
        $role->delete();

        return response()->json(null, 204);
    }

    /**
     * Validate role data for update.
     *
     * @param Request $request
     *
     * @param Role $role
     *
     * @return array The validated data.
     */
    protected function validateRoleData(Request $request, Role $role): array
    {
        return $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('roles', 'name')->ignore($role->id),
            ],
            'label' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);
    }

    /**
     * Sync permissions for the given role.
     *
     * @param Role $role
     *
     * @param array $data
     *
     * @return void
     */
    protected function syncPermissions(Role $role, array $data): void
    {
        if (isset($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }
    }
}
