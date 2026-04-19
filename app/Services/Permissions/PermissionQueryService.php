<?php

namespace App\Services\Permissions;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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
     * @return array
     */
    public function list(Request $request): array
    {
        $query = Permission::with('roles');

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $this->paginate($query, $request);

        return array_merge(
            $paginator,
            ['permissions' => $this->getPermissions()]
        );
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
     * Paginate and transform the permission query.
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
            ->through(fn (
                Permission $permission
            ): array => $this->formatPermission($permission))
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
            'create' => Gate::allows('create', Permission::class),
            'viewAny' => Gate::allows('viewAny', Permission::class),
        ];
    }

    /**
     * Format a permission into a structured array.
     *
     * Combines core attributes, derived flags, relationships, and permissions.
     *
     * @param  Permission $permission
     *
     * @return array
     */
    private function formatPermission(Permission $permission): array
    {
        return array_merge(
            $this->baseData($permission),
            $this->derivedData($permission),
            $this->relationshipData($permission),
            $this->permissionData($permission),
        );
    }

    /**
     * Extract core permission attributes.
     *
     * @param  Permission $permission
     *
     * @return array
     */
    private function baseData(Permission $permission): array
    {
        return [
            'id' => $permission->id,
            'name' => $permission->name,
            'label' => $permission->label,
        ];
    }

    /**
     * Extract derived flags for the permission.
     *
     * @param  Permission $permission
     *
     * @return array
     */
    private function derivedData(Permission $permission): array
    {
        return [
            'is_assigned' => $permission->getIsAssignedAttribute(),
            'role_count' => $permission->getRoleCountAttribute(),
        ];
    }

    /**
     * Extract related role and creator data for the permission.
     *
     * @param  Permission $permission
     *
     * @return array
     */
    private function relationshipData(Permission $permission): array
    {
        return [
            'roles' => $permission->roles,
            'creator' => $permission->creator,
        ];
    }

    /**
     * Determine authorisation permissions for the permission.
     *
     * @param  Permission $permission
     *
     * @return array
     */
    private function permissionData(Permission $permission): array
    {
        return [
            'permissions' => [
                'view' => Gate::allows('view', $permission),
                'update' => Gate::allows('update', $permission),
                'delete' => Gate::allows('delete', $permission),
            ],
        ];
    }
}
