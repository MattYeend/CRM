<?php

namespace App\Services\PipelineStages;

use App\Models\PipelineStage;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

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

        $paginator = $query->paginate($perPage)->appends($request->query());

        $paginator->through([$this, 'formatPipelineStage']);

        $paginator->appends([
            'permissions' => [
                'create' => Gate::allows('create', PipelineStage::class),
                'viewAny' => Gate::allows('viewAny', PipelineStage::class),
            ],
        ]);

        return $paginator;
    }

    /**
     * Return a single pipeline stage with related data loaded.
     *
     * @param  PipelineStage $pipelineStage The route-model-bound pipeline stage
     * instance.
     *
     * @return array
     */
    public function show(PipelineStage $pipelineStage): array
    {
        $pipelineStage->load('pipeline');

        return $this->formatPipelineStage($pipelineStage);
    }

    /**
     * Format a pipeline stage into a structured array.
     *
     * Includes core attributes, related pipeline data, derived state flags,
     * and authorisation permissions for the current user.
     *
     * @param  PipelineStage $pipelineStage
     *
     * @return array
     */
    private function formatPipelineStage(PipelineStage $pipelineStage): array
    {
        return [
            'id' => $pipelineStage->id,
            'pipeline_id' => $pipelineStage->pipeline_id,
            'pipeline' => $pipelineStage->pipeline,
            'name' => $pipelineStage->name,
            'position' => $pipelineStage->position,
            'is_won_stage' => $pipelineStage->is_won_stage,
            'is_lost_stage' => $pipelineStage->is_lost_stage,
            'is_open' => $pipelineStage->getIsOpenAttribute(),
            'is_won' => $pipelineStage->getIsWonAttribute(),
            'is_lost' => $pipelineStage->getIsLostAttribute(),
            'deal_count' => $pipelineStage->getDealCountAttribute(),
            'creator' => $pipelineStage->creator,
            'created_at' => $pipelineStage->created_at,
            'permissions' => [
                'view' => Gate::allows('view', $pipelineStage),
                'update' => Gate::allows('update', $pipelineStage),
                'delete' => Gate::allows('delete', $pipelineStage),
            ],
        ];
    }
}
