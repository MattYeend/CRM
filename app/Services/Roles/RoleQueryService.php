<?php

namespace App\Services\Roles;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

/**
 * Handles read queries for Role records.
 *
 * Delegates searching, sorting, and trash filtering to dedicated
 * sub-services and returns paginated or single role results with
 * the appropriate relationships loaded.
 */
class RoleQueryService
{
    /**
     * Service responsible for applying sort role.
     *
     * @var RoleSortingService
     */
    private RoleSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters.
     *
     * @var RoleTrashFilterService
     */
    private RoleTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  RoleSortingService $sorting Handles sort order.
     * @param  RoleTrashFilterService $trashFilter Handles
     * trash filtering.
     */
    public function __construct(
        RoleSortingService $sorting,
        RoleTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of roles with search, sorting,
     * and trash filters applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry search,
     * sort, filter, and pagination params.
     *
     * @return LengthAwarePaginator Paginated roles item results.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = Role::withCount('users')
            ->with('permissions');

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $query->paginate($perPage)->appends($request->query());

        $paginator->through(
            fn (Role $role) => $this->formatRole($role)
        );

        $paginator->appends([
            'permissions' => [
                'create' => Gate::allows('create', Role::class),
                'viewAny' => Gate::allows('viewAny', Role::class),
            ],
        ]);

        return $paginator;
    }

    /**
     * Return a single role with related data loaded.
     *
     * @param  Role $role The route-model-bound role
     * instance.
     *
     * @return array
     */
    public function show(Role $role): array
    {
        $role->load('permissions', 'users');

        return $this->formatRole($role);
    }

    /**
     * Format a role into a structured array.
     *
     * Includes core attributes, related permission and user data, derived
     * admin state flags, and authorisation permissions for the current user.
     *
     * @param  Role $role
     *
     * @return array
     */
    private function formatRole(Role $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'label' => $role->label,
            'is_admin' => $role->getIsAdminAttribute(),
            'is_super_admin' => $role->getIsSuperAdminAttribute(),
            'user_count' => $role->users_count ??
                $role->getUserCountAttribute(),
            'permissions' => $role->permissions,
            'users' => $role->relationLoaded('users') ? $role->users : null,
            'permissions_meta' => [
                'view' => Gate::allows('view', $role),
                'update' => Gate::allows('update', $role),
                'delete' => Gate::allows('delete', $role),
            ],
        ];
    }
}
