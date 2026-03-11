<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\Users\UserLogService;
use App\Services\Users\UserManagementService;
use App\Services\Users\UserQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Declare a protected property to hold the UserLogService,
     * UserQueryService, and UserManagementService instances
     *
     * @var UserLogService
     * @var UserManagementService
     * @var UserQueryService
     */
    private UserLogService $logger;
    private UserManagementService $management;
    private UserQueryService $query;

    /**
     * Constructor for the controller
     *
     * @param UserLogService $logger
     * @param UserManagementService $management
     * @param UserQueryService $query
     *
     * An instance of the UserLogService used for logging
     * user-related actions
     * An instance of the UserManagementService for management
     * of users
     * An instance of the UserQueryService for the query of
     * user-related actions
     */
    public function __construct(
        UserLogService $logger,
        UserManagementService $management,
        UserQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
    }

    /**
     * Display a listing of the users.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $users = $this->query->list($request);

        return response()->json($users);
    }

    /**
     * Display the specified user.
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        $user = $this->query->show($user);

        return response()->json($user);
    }

    /**
     * Store a newly created user in storage.
     *
     * @param StoreUserRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->management->store($request);
        $auth = auth()->user();
        $this->logger->userCreated(
            $auth,
            $auth->id,
            $user,
        );

        return response()->json($user, 201);
    }

    /**
     * Update the specified user in storage.
     *
     * @param UpdateUserRequest $request
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user = $this->management->update($request, $user);

        $auth = auth()->user();

        $this->logger->userUpdated(
            $user,
            $auth->id,
            $user,
        );

        return response()->json($user);
    }

    /**
     * Remove the specified user from storage (soft delete).
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);
        $auth = auth()->user();

        $this->logger->userDeleted(
            $auth,
            $auth->id,
            $user,
        );

        $this->management->destroy($user);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified user from soft deletion.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function restore($id): JsonResponse
    {
        $user = $this->management->restore((int) $id);

        $this->authorize('restore', $user);
        $auth = auth()->user();

        $this->logger->userRestored(
            $auth,
            $auth->id,
            $user,
        );

        return response()->json($user);
    }

    /**
     * Permanently delete the specified user from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function forceDelete($id): JsonResponse
    {
        $user = User::withTrashed()->find((int) $id);

        $auth = auth()->user();

        $this->logger->userForceDeleted(
            $auth,
            $auth->id,
            $user,
        );

        $this->management->forceDelete((int) $id);

        return response()->json(null, 204);
    }

    /**
     * Attach roles to the specified user without detaching existing ones.
     *
     * @param Request $request
     *
     * @param User $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function attachRoles(Request $request, User $user): JsonResponse
    {
        $user = $this->management->attachRoles($request, $user);

        return response()->json($user);
    }

    /**
     * Detach roles from the specified user.
     *
     * @param Request $request
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    public function detachRoles(Request $request, User $user): JsonResponse
    {
        $user = $this->management->detachRoles($request, $user);

        return response()->json($user);
    }
}
