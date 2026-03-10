<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class RoleQueryService
{
    private RoleSortingService $sorting;
    private TrashFilterService $trashFilter;
    public function __construct(
        RoleSortingService $sorting,
        TrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated role, applying filters/sorting.
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

        $query = Role::withCount('users')
            ->with('permissions');

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single role.
     *
     * @param Role $role
     *
     * @return Role
     */
    public function show(Role $role): Role
    {
        return $role->load('permissions', 'users');
    }
}
