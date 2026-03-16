<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single user with roles loaded.
     *
     * @param User $user
     *
     * @return User
     */
    public function show(User $user): User
    {
        return $user->load('role', 'jobTitle');
    }
}
