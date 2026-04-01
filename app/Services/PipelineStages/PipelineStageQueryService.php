<?php

namespace App\Services\PipelineStages;

use App\Models\PipelineStage;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Handles read queries for PipelineStage records.
 *
 * Delegates searching, sorting, and trash filtering to dedicated
 * sub-services and returns paginated or single pipeline stage results with
 * the appropriate relationships loaded.
 */
class PipelineStageQueryService
{
    /**
     * Service responsible for applying sort order.
     *
     * @var PipelineSortingService
     */
    private PipelineStageSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters.
     *
     * @var PipelineStageTrashFilterService
     */
    private PipelineStageTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  PipelineStageSortingService $sorting Handles sort order.
     * @param  PipelineStageTrashFilterService $trashFilter Handles
     * trash filtering.
     */
    public function __construct(
        PipelineStageSortingService $sorting,
        PipelineStageTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of pipeline stages with search, sorting,
     * and trash filters applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry search,
     * sort, filter, and pagination params.
     *
     * @return LengthAwarePaginator Paginated pipeline stages item results.
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
     * Return a single pipeline stage with related data loaded.
     *
     * @param  PipelineStage $pipelineStage The route-model-bound pipeline stage
     * instance.
     *
     * @return PipelineStage The pipeline stage with relationships loaded.
     */
    public function show(PipelineStage $pipelineStage): PipelineStage
    {
        return $pipelineStage->load('pipeline');
    }
}
