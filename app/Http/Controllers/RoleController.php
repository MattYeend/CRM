<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
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
        $data = $request->validate([
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

        $role->update($request->only(['name', 'label']));

        if (isset($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }

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
        $role->permissions()->detach();
        $role->users()->detach();
        $role->delete();

        return response()->json(null, 204);
    }
}
