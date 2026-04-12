<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\Roles\RoleLogService;
use App\Services\Roles\RoleManagementService;
use App\Services\Roles\RoleQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles HTTP requests for the Role resource.
 *
 * Delegates business logic to three dedicated services:
 *   - RoleLogService — records audit log entries for role changes
 *   - RoleManagementService — handles permission sync and other role
 *      management operations
 *   - RoleQueryService — handles read/list queries with filtering and
 *      pagination
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class RoleController extends Controller
{
    /**
     * Service responsible for writing audit log entries for role events.
     *
     * @var RoleLogService
     */
    protected RoleLogService $logger;

    /**
     * Service responsible for managing roles, including permission
     * synchronisation.
     *
     * @var RoleManagementService
     */
    protected RoleManagementService $management;

    /**
     * Service responsible for querying and listing roles.
     *
     * @var RoleQueryService
     */
    protected RoleQueryService $query;

    /**
     * Inject the required services into the controller.
     *
     * @param RoleLogService $logger Handles audit logging for role events.
     * @param RoleManagementService $management Handles role management
     * including permission sync.
     * @param RoleQueryService $query Handles role listing and retrieval.
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
     * Display a listing of the resource.
     *
     * Returns roles with their associated user counts and permissions.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated role data with user counts and
     * permissions.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        $roles = $this->query->list($request);

        return response()->json($roles);
    }

    /**
     * Display the specified resource.
     *
     * Returns a single role by its model binding, including its associated
     * permissions and users.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param Role $role Route-model-bound role instance.
     *
     * @return JsonResponse The resolved role resource with permissions and
     * users.
     */
    public function show(Role $role): JsonResponse
    {
        $this->authorize('view', $role);
        $this->authorize('access', $role);

        $role = $this->query->show($role);

        return response()->json($role);
    }

    /**
     * Sync permissions for the specified role.
     *
     * Delegates to the role management service to replace the role's current
     * permission set with the provided data.
     *
     * @param Role $role The role instance whose permissions should be synced.
     * @param array $data The complete set of permissions to sync against the
     * role.
     *
     * @return void
     */
    public function syncPermissions(Role $role, array $data): void
    {
        $this->management->syncPermissions($role, $data);
    }
}
