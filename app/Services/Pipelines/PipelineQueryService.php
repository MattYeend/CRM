<?php

namespace App\Services\Pipelines;

use App\Models\Pipeline;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
     * @return array
     */
    public function list(Request $request): array
    {
        $query = Pipeline::with('stages');

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $this->paginate($query, $request);

        return array_merge(
            $paginator,
            ['permissions' => $this->getPermissions()]
        );
    }

    /**
     * Return a single pipeline with related data loaded.
     *
     * @param  Pipeline $pipeline The route-model-bound pipeline
     * instance.
     *
     * @return array
     */
    public function show(Pipeline $pipeline): array
    {
        $pipeline->load('stages');

        return $this->formatPipeline($pipeline);
    }

    /**
     * Paginate and transform the pipeline query.
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
                Pipeline $pipeline
            ): array => $this->formatPipeline($pipeline))
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
            'create' => Gate::allows('create', Pipeline::class),
            'viewAny' => Gate::allows('viewAny', Pipeline::class),
        ];
    }

    /**
     * Format a pipeline into a structured array.
     *
     * Combines core attributes, derived flags, relationships, and permissions.
     *
     * @param  Pipeline $pipeline
     *
     * @return array
     */
    private function formatPipeline(Pipeline $pipeline): array
    {
        return array_merge(
            $this->baseData($pipeline),
            $this->derivedData($pipeline),
            $this->relationshipData($pipeline),
            $this->permissionData($pipeline),
        );
    }

    /**
     * Extract core pipeline attributes.
     *
     * @param  Pipeline $pipeline
     *
     * @return array
     */
    private function baseData(Pipeline $pipeline): array
    {
        return [
            'id' => $pipeline->id,
            'name' => $pipeline->name,
            'description' => $pipeline->description,
        ];
    }

    /**
     * Extract derived flags for the pipeline.
     *
     * @param  Pipeline $pipeline
     *
     * @return array
     */
    private function derivedData(Pipeline $pipeline): array
    {
        return [
            'is_default' => $pipeline->getIsDefaultAttribute(),
            'stage_count' => $pipeline->getStageCountAttribute(),
            'deal_count' => $pipeline->getDealCountAttribute(),
        ];
    }

    /**
     * Extract related stage and creator data for the pipeline.
     *
     * @param  Pipeline $pipeline
     *
     * @return array
     */
    private function relationshipData(Pipeline $pipeline): array
    {
        return [
            'stages' => $pipeline->stages,
            'creator' => $pipeline->creator,
        ];
    }

    /**
     * Determine authorisation permissions for the pipeline.
     *
     * @param  Pipeline $pipeline
     *
     * @return array
     */
    private function permissionData(Pipeline $pipeline): array
    {
        return [
            'permissions' => [
                'view' => Gate::allows('view', $pipeline),
                'update' => Gate::allows('update', $pipeline),
                'delete' => Gate::allows('delete', $pipeline),
            ],
        ];
    }
}
