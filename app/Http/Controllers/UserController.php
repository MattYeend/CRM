<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserLogService;
use App\Services\UserManagementService;
use App\Services\UserQueryService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Declare a protected property to hold the UserLogService,
     * UserQueryService, and UserManagementService instances
     *
     * @var UserLogService
     * @var UserQueryService
     * @var UserManagementService
     */
    private UserLogService $logger;
    private UserManagementService $managementService;
    private UserQueryService $queryService;

    public function __construct(
        UserLogService $logger,
        UserManagementService $managementService,
        UserQueryService $queryService
    ) {
        $this->logger = $logger;
        $this->managementService = $managementService;
        $this->queryService = $queryService;
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

        $this->logger->userCreated(
            auth()->user(),
            auth()->id(),
            $user
        );

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

        $this->logger->userUpdated(
            auth()->user(),
            auth()->id(),
            $user
        );

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

        $this->logger->userDeleted(
            auth()->user(),
            auth()->id(),
            $user
        );

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

        $this->logger->userRestored(
            auth()->user(),
            auth()->id(),
            $user
        );

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
        $user = User::withTrashed()->find((int) $id);

        $this->managementService->forceDelete((int) $id);

        $this->logger->userForceDeleted(
            auth()->user(),
            auth()->id(),
            $user
        );

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
