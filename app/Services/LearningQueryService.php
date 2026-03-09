<?php

namespace App\Services;

use App\Models\Learning;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class LearningQueryService
{
    private LearningSortingService $sorting;
    private TrashFilterService $trashFilter;
    public function __construct(
        LearningSortingService $sorting,
        TrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated learning, applying filters/sorting.
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

        $query = Learning::with(
            'users',
        );

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single learning.
     *
     * @param Learning $learning
     *
     * @return Learning
     */
    public function show(Learning $learning): Learning
    {
        return $learning->load('users');
    }
}
