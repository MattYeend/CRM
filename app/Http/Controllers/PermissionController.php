<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    /**
     * Display a listing of the permissions with their roles.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
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
    public function show(Permission $permission)
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
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:permissions,name',
            'label' => 'nullable|string',
        ]);

        $permission = Permission::create($data);

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
    public function update(Request $request, Permission $permission)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                Rule::unique('permissions', 'name')->ignore($permission->id),
            ],
            'label' => 'nullable|string',
        ]);

        $permission->update($data);

        return response()->json($permission);
    }

    /**
     * Remove the specified permission from storage.
     *
     * @param Permission $permission
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Permission $permission)
    {
        $permission->roles()->detach();
        $permission->delete();

        return response()->json(null, 204);
    }
}
