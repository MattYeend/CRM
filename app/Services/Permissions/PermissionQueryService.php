<?php

namespace App\Services\Permissions;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single permission with related data loaded.
     *
     * @param  Permission $ordpermissioner The route-model-bound permission
     * instance.
     *
     * @return Permission The permission with relationships loaded.
     */
    public function show(Permission $permission): Permission
    {
        return $permission->load('roles');
    }
}
