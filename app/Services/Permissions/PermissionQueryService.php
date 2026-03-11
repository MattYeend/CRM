<?php

namespace App\Services\Permissions;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PermissionQueryService
{
    private PermissionSortingService $sorting;
    private PermissionTrashFilterService $trashFilter;
    public function __construct(
        PermissionSortingService $sorting,
        PermissionTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated permission, applying filters/sorting.
     *
     * @param Request $request
     *
     * @return LengthAwarePaginator
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
     * Return a single permission.
     *
     * @param Permission $permission
     *
     * @return Permission
     */
    public function show(Permission $permission): Permission
    {
        return $permission->load('roles');
    }
}
