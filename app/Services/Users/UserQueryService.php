<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

class UserQueryService
{
    private UserSortingService $sorting;
    private UserTrashFilterService $trashFilter;
    public function __construct(
        UserSortingService $sorting,
        UserTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated users with roles, applying filters/sorting.
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

        $query = User::with('role', 'jobTitle');
        $this->trashFilter->applyTrashFilters($query, $request);
        $this->sorting->applySorting($query, $request);

        $paginator = $query->paginate($perPage)->appends($request->query());

        $paginator->through(fn (User $user) => $this->formatUser($user));

        $paginator->appends([
            'permissions' => [
                'create' => Gate::allows('create', User::class),
                'viewAny' => Gate::allows('viewAny', User::class),
            ],
        ]);

        return $paginator;
    }

    /**
     * Return a single user with roles loaded.
     *
     * @param User $user
     *
     * @return array
     */
    public function show(User $user): array
    {
        return $this->formatUser($user);
    }

    /**
     * Format a user into an array with role, job title, and permissions.
     *
     * @param User $user
     *
     * @return array
     */
    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url,
            'job_title' => $user->jobTitle,
            'role' => $user->role,
            'permissions' => [
                'view' => Gate::allows('view', $user),
                'update' => Gate::allows('update', $user),
                'delete' => Gate::allows('delete', $user),
            ],
        ];
    }
}
