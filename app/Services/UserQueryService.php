<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class UserQueryService
{
    /**
     * Return paginated users with roles, applying filters/sorting.
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request)
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = User::with('roles');

        $this->applyTrashFilters($query, $request);
        $this->applySorting($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single user with roles loaded.
     *
     * @param User $user
     *
     * @return User
     */
    public function show(User $user)
    {
        return $user->load('roles');
    }

    /**
     * Apply trash filters to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @param Request $request
     *
     * @return void
     */
    private function applyTrashFilters($query, Request $request): void
    {
        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();

            return;
        }

        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }
    }

    /**
     * Apply sorting to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @param Request $request
     *
     * @return void
     */
    private function applySorting($query, Request $request): void
    {
        $allowedSorts = [
            'id',
            'name',
            'email',
            'created_at',
            'updated_at',
        ];

        $sortBy = $request->query('sort_by', 'id');

        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id';
        }

        $sortDir = $request->query('sort_dir', 'desc') === 'asc'
            ? 'asc'
            : 'desc';

        $query->orderBy($sortBy, $sortDir);
    }
}
