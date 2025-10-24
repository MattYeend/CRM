<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserManagementService;
use App\Services\UserQueryService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private UserQueryService $queryService;
    private UserManagementService $managementService;

    public function __construct(
        UserQueryService $queryService,
        UserManagementService $managementService
    ) {
        $this->queryService = $queryService;
        $this->managementService = $managementService;
    }

    /**
     * Display a listing of the users.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $users = $this->queryService->list($request);

        return response()->json($users);
    }

    /**
     * Display the specified user.
     *
     * @param User $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(User $user)
    {
        return response()->json($this->queryService->show($user));
    }

    /**
     * Store a newly created user in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = $this->managementService->store($request);

        return response()->json($user, 201);
    }

    /**
     * Update the specified user in storage.
     *
     * @param Request $request
     *
     * @param User    $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        $user = $this->managementService->update($request, $user);

        return response()->json($user);
    }

    /**
     * Remove the specified user from storage (soft delete).
     *
     * @param User $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $user)
    {
        $this->managementService->destroy($user);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified user from soft deletion.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        $user = $this->managementService->restore((int) $id);

        return response()->json($user);
    }

    /**
     * Permanently delete the specified user from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDelete($id)
    {
        $this->managementService->forceDelete((int) $id);

        return response()->json(null, 204);
    }

    /**
     * Attach roles to the specified user without detaching existing ones.
     *
     * @param Request $request
     *
     * @param User    $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function attachRoles(Request $request, User $user)
    {
        $user = $this->managementService->attachRoles($request, $user);

        return response()->json($user);
    }

    /**
     * Detach roles from the specified user.
     *
     * @param Request $request
     *
     * @param User    $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detachRoles(Request $request, User $user)
    {
        $user = $this->managementService->detachRoles($request, $user);

        return response()->json($user);
    }
}
