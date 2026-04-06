<?php

namespace App\Services\Permissions;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

/**
 * Handles read queries for Permission records.
 *
 * Delegates searching, sorting, and trash filtering to dedicated
 * sub-services and returns paginated or single permission results with
 * the appropriate relationships loaded.
 */
class PermissionQueryService
{
    /**
     * Service responsible for applying sort order.
     *
     * @var PermissionSortingService
     */
    private PermissionSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters.
     *
     * @var PermissionTrashFilterService
     */
    private PermissionTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  PermissionSortingService $sorting Handles sort order.
     * @param  PermissionTrashFilterService $trashFilter Handles
     * trash filtering.
     */
    public function __construct(
        PermissionSortingService $sorting,
        PermissionTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of permissions with search, sorting,
     * and trash filters applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry search,
     * sort, filter, and pagination params.
     *
     * @return LengthAwarePaginator Paginated permissions item results.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = Permission::with('roles');

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $query->paginate($perPage)->appends($request->query());

        $paginator->through(
            fn (Permission $permission) => $this->formatPermission($permission)
        );

        $paginator->appends([
            'permissions' => [
                'create' => Gate::allows('create', Permission::class),
                'viewAny' => Gate::allows('viewAny', Permission::class),
            ],
        ]);

        return $paginator;
    }

    /**
     * Return a single permission with related data loaded.
     *
     * @param  Permission $permission The route-model-bound permission
     * instance.
     *
     * @return array
     */
    public function show(Permission $permission): array
    {
        $permission->load('roles');

        return $this->formatPermission($permission);
    }

    /**
     * Format a permission into a structured array.
     *
     * Includes core attributes, related role data, derived assignment state,
     * and authorisation permissions for the current user.
     *
     * @param  Permission $permission
     *
     * @return array
     */
    private function formatPermission(Permission $permission): array
    {
        return [
            'id' => $permission->id,
            'name' => $permission->name,
            'label' => $permission->label,
            'is_assigned' => $permission->getIsAssignedAttribute(),
            'role_count' => $permission->getRoleCountAttribute(),
            'roles' => $permission->roles,
            'creator' => $permission->creator,
            'permissions' => [
                'view' => Gate::allows('view', $permission),
                'update' => Gate::allows('update', $permission),
                'delete' => Gate::allows('delete', $permission),
            ],
        ];
    }
}
