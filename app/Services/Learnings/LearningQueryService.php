<?php

namespace App\Services\Learnings;

use App\Models\Learning;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

/**
 * Handles read queries for Learning records.
 *
 * Delegates searching, sorting, and trash filtering to dedicated
 * sub-services and returns paginated or single learning results with
 * the appropriate relationships loaded.
 */
class LearningQueryService
{
    /**
     * Service responsible for applying sort order.
     *
     * @var LearningSortingService
     */
    private LearningSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters.
     *
     * @var LearningTrashFilterService
     */
    private LearningTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  LearningSortingService $sorting Handles sort order.
     * @param  LearningTrashFilterService $trashFilter Handles
     * trash filtering.
     */
    public function __construct(
        LearningSortingService $sorting,
        LearningTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of learning with search, sorting,
     * and trash filters applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry search,
     * sort, filter, and pagination params.
     *
     * @return LengthAwarePaginator Paginated learning item results.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = Learning::with(
            'users',
        );

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $query->paginate($perPage)->appends($request->query());

        $paginator->through(
            fn (Learning $learning) => $this->formatLearning($learning)
        );

        $paginator->appends([
            'permissions' => [
                'create' => Gate::allows('create', Learning::class),
                'viewAny' => Gate::allows('viewAny', Learning::class),
            ],
        ]);

        return $paginator;
    }

    /**
     * Return a single learning with related data loaded.
     *
     * @param  Learning $learning The route-model-bound learning
     * instance.
     *
     * @return array
     */
    public function show(Learning $learning): array
    {
        $learning->load(
            'users',
            'questions',
        );

        return $this->formatLearning($learning);
    }

    /**
     * Format a learning into a structured array.
     *
     * Includes core attributes, related data, derived accessors,
     * and authorisation permissions for the current user.
     *
     * @param  Learning $learning
     *
     * @return array
     */
    private function formatLearning(Learning $learning): array
    {
        return [
            'id' => $learning->id,
            'title' => $learning->title,
            'description' => $learning->description,
            'date' => $learning->date,
            'pass_score' => $learning->pass_score,
            'meta_title' => $learning->meta_title,
            'meta_description' => $learning->meta_description,
            'meta_keywords' => $learning->meta_keywords,
            'meta_author' => $learning->meta_author,
            'users' => $learning->users,
            'questions' => $learning->questions,
            'creator' => $learning->creator,
            'permissions' => [
                'view' => Gate::allows('view', $learning),
                'update' => Gate::allows('update', $learning),
                'delete' => Gate::allows('delete', $learning),
            ],
        ];
    }
}
