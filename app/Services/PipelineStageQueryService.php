<?php

namespace App\Services;

use App\Models\PipelineStage;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PipelineStageQueryService
{
    private PipelineStageSortingService $sorting;
    private TrashFilterService $trashFilter;
    public function __construct(
        PipelineStageSortingService $sorting,
        TrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated pipeline stage, applying filters/sorting.
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

        $query = PipelineStage::with('pipeline');

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single pipeline stage.
     *
     * @param PipelineStage $pipelineStage
     *
     * @return PipelineStage
     */
    public function show(PipelineStage $pipelineStage): PipelineStage
    {
        return $pipelineStage->load('pipeline');
    }
}
