<?php

namespace App\Services\Roles;

use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Handles read queries for Role records.
 *
 * Delegates sorting and trash filtering to dedicated sub-services
 * and returns paginated or single role results with the appropriate
 * relationships loaded.
 */
class RoleQueryService
{
    /**
     * Service responsible for applying sort order.
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
     * @param  RoleTrashFilterService $trashFilter Handles trash filtering.
     */
    public function __construct(
        RoleSortingService $sorting,
        RoleTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of roles with sorting and trash filters applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry sort,
     * filter, and pagination params.
     *
     * @return array
     */
    public function list(Request $request): array
    {
        $query = Role::withCount('users')
            ->with('permissions');

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $this->paginate($query, $request);

        return array_merge(
            $paginator,
            ['permissions' => $this->getPermissions()]
        );
    }

    /**
     * Return a single role with related data loaded.
     *
     * @param  Role $role The route-model-bound role instance.
     *
     * @return array
     */
    public function show(Role $role): array
    {
        $role->load('permissions', 'users');

        return $this->formatRole($role);
    }

    /**
     * Paginate and transform the role query.
     *
     * @param Builder $query
     * @param Request $request
     *
     * @return array
     */
    private function paginate($query, Request $request): array
    {
        $perPage = max(1, min((int) $request->query('per_page', 10), 100));

        return $query->paginate($perPage)
            ->appends($request->query())
            ->through(fn (Role $role): array => $this->formatRole($role))
            ->toArray();
    }

    /**
     * Get permission flags for the current user.
     *
     * @return array
     */
    private function getPermissions(): array
    {
        return [
            'create' => Gate::allows('create', Role::class),
            'viewAny' => Gate::allows('viewAny', Role::class),
        ];
    }

    /**
     * Format a role into a structured array.
     *
     * Combines core attributes, derived flags, relationships, and permissions.
     *
     * @param  Role $role
     *
     * @return array
     */
    private function formatRole(Role $role): array
    {
        return array_merge(
            $this->baseData($role),
            $this->derivedData($role),
            $this->relationshipData($role),
            $this->permissionData($role),
        );
    }

    /**
     * Extract core role attributes.
     *
     * @param  Role $role
     *
     * @return array
     */
    private function baseData(Role $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'label' => $role->label,
        ];
    }

    /**
     * Extract derived flags for the role.
     *
     * @param  Role $role
     *
     * @return array
     */
    private function derivedData(Role $role): array
    {
        return [
            'is_admin' => $role->is_admin,
            'is_super_admin' => $role->is_super_admin,
            'user_count' => $role->users_count ??
                $role->getUserCountAttribute(),
        ];
    }

    /**
     * Extract related permissions and users for the role.
     *
     * @param  Role $role
     *
     * @return array
     */
    private function relationshipData(Role $role): array
    {
        return [
            'permissions' => $role->permissions,
            'users' => $role->relationLoaded('users') ? $role->users : null,
        ];
    }

    /**
     * Determine authorisation permissions for the role.
     *
     * @param  Role $role
     *
     * @return array
     */
    private function permissionData(Role $role): array
    {
        return [
            'permissions_meta' => [
                'view' => Gate::allows('view', $role),
                'update' => Gate::allows('update', $role),
                'delete' => Gate::allows('delete', $role),
            ],
        ];
    }
}
