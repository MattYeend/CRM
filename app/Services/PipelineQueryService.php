<?php

namespace App\Services;

use App\Models\Pipeline;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PipelineQueryService
{
    private PipelineSortingService $sorting;
    private TrashFilterService $trashFilter;
    public function __construct(
        PipelineSortingService $sorting,
        TrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated pipeline, applying filters/sorting.
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

        $query = Pipeline::with('stages');

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single pipeline.
     *
     * @param Pipeline $pipeline
     *
     * @return Pipeline
     */
    public function show(Pipeline $pipeline): Pipeline
    {
        return $pipeline->load('stages');
    }
}
