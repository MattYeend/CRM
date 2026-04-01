<?php

namespace App\Services\Roles;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single role with related data loaded.
     *
     * @param  Role $role The route-model-bound role
     * instance.
     *
     * @return Role The role with relationships loaded.
     */
    public function show(Role $role): Role
    {
        return $role->load('permissions', 'users');
    }
}
