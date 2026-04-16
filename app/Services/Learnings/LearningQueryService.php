<?php

namespace App\Services\Learnings;

use App\Models\Learning;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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
     * @return array Paginated learning item results.
     */
    public function list(Request $request): array
    {
        $query = Learning::with([
            'users' => function ($q) {
                $q->withPivot([
                    'is_complete',
                    'score',
                    'completed_at',
                ]);
            },
            'questions.answers',
        ]);

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $this->paginate($query, $request);

        return array_merge(
            $paginator,
            ['permissions' => $this->getPermissions()]
        );
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
        $learning->load([
            'users' => function ($q) {
                $q->withPivot([
                    'is_complete',
                    'score',
                    'completed_at',
                ]);
            },
            'questions.answers',
        ]);

        return $this->formatLearning($learning);
    }

    /**
     * Paginate and transform the learning query.
     *
     * @param  Builder $query
     * @param  Request $request
     *
     * @return array
     */
    private function paginate(Builder $query, Request $request): array
    {
        $perPage = max(1, min((int) $request->query('per_page', 10), 100));

        return $query->paginate($perPage)
            ->appends($request->query())
            ->through(fn (
                Learning $learning
            ): array => $this->formatLearning($learning))
            ->toArray();
    }

    /**
     * Get global permission flags for the current user.
     *
     * @return array
     */
    private function getPermissions(): array
    {
        return [
            'create' => Gate::allows('create', Learning::class),
            'viewAny' => Gate::allows('viewAny', Learning::class),
        ];
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
        $currentUser = $learning->users
            ->firstWhere('id', auth()->id());

        return array_merge(
            $this->baseFields($learning),
            $this->metaFields($learning),
            $this->relations($learning),
            $this->currentUserPivot($currentUser),
            $this->permissions($learning),
        );
    }

    /**
     * Base fields.
     *
     * @param  Learning $learning
     *
     * @return array
     */
    private function baseFields(Learning $learning): array
    {
        return [
            'id' => $learning->id,
            'title' => $learning->title,
            'description' => $learning->description,
            'date' => $learning->date,
            'pass_score' => $learning->pass_score,
        ];
    }

    /**
     * Meta fields.
     *
     * @param  Learning $learning
     *
     * @return array
     */
    private function metaFields(Learning $learning): array
    {
        return [
            'meta_title' => $learning->meta_title,
            'meta_description' => $learning->meta_description,
            'meta_keywords' => $learning->meta_keywords,
            'meta_author' => $learning->meta_author,
        ];
    }

    /**
     * Relations.
     *
     * @param  Learning $learning
     *
     * @return array
     */
    private function relations(Learning $learning): array
    {
        return [
            'users' => $learning->users,
            'questions' => $learning->questions,
            'creator' => $learning->creator,
        ];
    }

    /**
     * Current user pivot data.
     *
     * @param mixed $currentUser Pivoted user model or null.
     *
     * @return array
     */
    private function currentUserPivot($currentUser): array
    {
        return [
            'current_user' => $currentUser?->pivot ? [
                'is_complete' => $currentUser->pivot->is_complete,
                'score' => $currentUser->pivot->score,
                'completed_at' => $currentUser->pivot->completed_at,
            ] : null,
        ];
    }

    /**
     * Permissions for a learning.
     *
     * @param  Learning $learning
     *
     * @return array
     */
    private function permissions(Learning $learning): array
    {
        return [
            'permissions' => [
                'view' => Gate::allows('view', $learning),
                'update' => Gate::allows('update', $learning),
                'delete' => Gate::allows('delete', $learning),
            ],
        ];
    }
}
