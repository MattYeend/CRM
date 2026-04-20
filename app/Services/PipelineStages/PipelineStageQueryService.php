<?php

namespace App\Services\PipelineStages;

use App\Models\PipelineStage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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
     * @var PipelineStageSortingService
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
     * @return array
     */
    public function list(Request $request): array
    {
        $query = PipelineStage::with('pipeline');

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $this->paginate($query, $request);

        return array_merge(
            $paginator,
            ['permissions' => $this->getPermissions()]
        );
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
     * Paginate and transform the pipeline stage query.
     *
     * @param Builder $query
     * @param Request $request
     *
     * @return array
     */
    private function paginate($query, Request $request): array
    {
        $perPage = max(1, min((int) $request->query('per_page', 10), 100));

        return $query->paginate($perPage)
            ->appends($request->query())
            ->through(fn (
                PipelineStage $pipelineStage
            ): array => $this->formatPipelineStage($pipelineStage))
            ->toArray();
    }

    /**
     * Get permission flags for the current user.
     *
     * @return array
     */
    private function getPermissions(): array
    {
        return [
            'create' => Gate::allows('create', PipelineStage::class),
            'viewAny' => Gate::allows('viewAny', PipelineStage::class),
        ];
    }

    /**
     * Format a pipeline stage into a structured array.
     *
     * Combines core attributes, derived flags, relationships, and permissions.
     *
     * @param  PipelineStage $pipelineStage
     *
     * @return array
     */
    private function formatPipelineStage(PipelineStage $pipelineStage): array
    {
        return array_merge(
            $this->baseData($pipelineStage),
            $this->derivedData($pipelineStage),
            $this->relationshipData($pipelineStage),
            $this->permissionData($pipelineStage),
        );
    }

    /**
     * Extract core pipeline stage attributes.
     *
     * @param  PipelineStage $pipelineStage
     *
     * @return array
     */
    private function baseData(PipelineStage $pipelineStage): array
    {
        return [
            'id' => $pipelineStage->id,
            'pipeline_id' => $pipelineStage->pipeline_id,
            'name' => $pipelineStage->name,
            'position' => $pipelineStage->position,
            'is_won_stage' => $pipelineStage->is_won_stage,
            'is_lost_stage' => $pipelineStage->is_lost_stage,
        ];
    }

    /**
     * Extract derived flags for the pipeline stage.
     *
     * @param  PipelineStage $pipelineStage
     *
     * @return array
     */
    private function derivedData(PipelineStage $pipelineStage): array
    {
        return [
            'is_open' => $pipelineStage->getIsOpenAttribute(),
            'is_won' => $pipelineStage->getIsWonAttribute(),
            'is_lost' => $pipelineStage->getIsLostAttribute(),
            'deal_count' => $pipelineStage->getDealCountAttribute(),
        ];
    }

    /**
     * Extract related pipeline and creator data for the pipeline stage.
     *
     * @param  PipelineStage $pipelineStage
     *
     * @return array
     */
    private function relationshipData(PipelineStage $pipelineStage): array
    {
        return [
            'pipeline' => $pipelineStage->pipeline,
            'creator' => $pipelineStage->creator,
        ];
    }

    /**
     * Determine authorisation permissions for the pipeline stage.
     *
     * @param  PipelineStage $pipelineStage
     *
     * @return array
     */
    private function permissionData(PipelineStage $pipelineStage): array
    {
        return [
            'permissions' => [
                'view' => Gate::allows('view', $pipelineStage),
                'update' => Gate::allows('update', $pipelineStage),
                'delete' => Gate::allows('delete', $pipelineStage),
            ],
        ];
    }
}
