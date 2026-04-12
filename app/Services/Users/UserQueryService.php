<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Handles read queries for User records.
 *
 * Delegates searching, sorting, and trash filtering to dedicated
 * sub-services and returns paginated or single user results with
 * the appropriate relationships loaded.
 */
class UserQueryService
{
    /**
     * Service responsible for applying sort order.
     *
     * @var UserSortingService
     */
    private UserSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters.
     *
     * @var UserTrashFilterService
     */
    private UserTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  UserSortingService $sorting Handles sort order.
     * @param  UserTrashFilterService $trashFilter Handles
     * trash filtering.
     */
    public function __construct(
        UserSortingService $sorting,
        UserTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of users with search, sorting,
     * and trash filters applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry search,
     * sort, filter, and pagination params.
     *
     * @return array Paginated users item results.
     */
    public function list(Request $request): array
    {
        $perPage = max(1, min((int) $request->query('per_page', 10), 100));

        $query = User::with('role', 'jobTitle');
        $this->trashFilter->applyTrashFilters($query, $request);
        $this->sorting->applySorting($query, $request);

        $paginator = $query->paginate($perPage)->appends($request->query());

        $paginator->through(fn (User $user) => $this->formatUser($user));

        $result = $paginator->toArray();

        $result['permissions'] = [
            'create' => Gate::allows('create', User::class),
            'viewAny' => Gate::allows('viewAny', User::class),
        ];

        return $result;
    }

    /**
     * Return a single user with related data loaded.
     *
     * @param  User $user The route-model-bound user
     * instance.
     *
     * @return User The user with relationships loaded.
     */
    public function show(User $user): array
    {
        return $this->formatUser($user);
    }

    /**
     * Transform a user model into a structured array.
     *
     * Includes basic user attributes along with related role, job title,
     * and computed permission flags based on authorization policies.
     *
     * @param  User $user The user instance to format.
     *
     * @return array<string,mixed> The formatted user data.
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
            'notes' => $user->notes,
            'tasks' => $user->tasks,
            'deals' => $user->deals,
            'activities' => $user->activity,
            'learnings' => $user->learnings,
            'permissions' => [
                'view' => Gate::allows('view', $user),
                'update' => Gate::allows('update', $user),
                'delete' => Gate::allows('delete', $user),
            ],
        ];
    }
}
