<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\Roles\RoleLogService;
use App\Services\Roles\RoleManagementService;
use App\Services\Roles\RoleQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Declare a protected property to hold the RoleLogService,
     * RoleManagementService and RoleQueryService instance
     *
     * @var RoleLogService
     * @var RoleManagementService
     * @var RoleQueryService
     */
    protected RoleLogService $logger;
    protected RoleManagementService $management;
    protected RoleQueryService $query;

    /**
     * Constructor for the controller
     *
     * @param RoleLogService $logger
     *
     * @param RoleManagementService $management
     *
     * @param RoleQueryService $query
     *
     * An instance of the RoleLogService used for logging
     * role-related actions
     * An instance of the RoleManagementService for management
     * of role
     * An instance of the RoleQueryService for the query of
     * role-related actions
     */
    public function __construct(
        RoleLogService $logger,
        RoleManagementService $management,
        RoleQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
    }

    /**
     * Display a listing of the roles with user counts and permissions.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        $role = $this->query->list($request);

        return response()->json($role);
    }

    /**
     * Display the specified role with its permissions and users.
     *
     * @param Role $role
     *
     * @return JsonResponse
     */
    public function show(Role $role): JsonResponse
    {
        $this->authorize('view', $role);

        $role = $this->query->show($role);

        return response()->json($role);
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
    public function syncPermissions(Role $role, array $data): void
    {
        $this->management->syncPermissions($role, $data);
    }
}
