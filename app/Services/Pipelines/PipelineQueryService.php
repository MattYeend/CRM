<?php

namespace App\Services\Pipelines;

use App\Models\Pipeline;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Handles read queries for Pipeline records.
 *
 * Delegates searching, sorting, and trash filtering to dedicated
 * sub-services and returns paginated or single pipeline results with
 * the appropriate relationships loaded.
 */
class PipelineQueryService
{
    /**
     * Service responsible for applying sort order.
     *
     * @var PipelineSortingService
     */
    private PipelineSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters.
     *
     * @var PipelineTrashFilterService
     */
    private PipelineTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  PipelineSortingService $sorting Handles sort order.
     * @param  PipelineTrashFilterService $trashFilter Handles
     * trash filtering.
     */
    public function __construct(
        PipelineSortingService $sorting,
        PipelineTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of pipelines with search, sorting,
     * and trash filters applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry search,
     * sort, filter, and pagination params.
     *
     * @return LengthAwarePaginator Paginated pipelines item results.
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
     * Return a single pipeline with related data loaded.
     *
     * @param  Pipeline $pipeline The route-model-bound pipeline
     * instance.
     *
     * @return Pipeline The pipeline with relationships loaded.
     */
    public function show(Pipeline $pipeline): Pipeline
    {
        return $pipeline->load('stages');
    }
}
