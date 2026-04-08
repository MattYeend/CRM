<?php

namespace App\Services\Industries;

use App\Models\Industry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Handles read queries for Industry records.
 *
 * Delegates searching, sorting, and trash filtering to dedicated
 * sub-services and returns paginated or single industry results with
 * the appropriate relationships loaded.
 */
class IndustryQueryService
{
    /**
     * Service responsible for applying search filters.
     *
     * @var IndustrySearchService
     */
    private IndustrySearchService $search;

    /**
     * Service responsible for applying sort order.
     *
     * @var IndustrySortingService
     */
    private IndustrySortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters.
     *
     * @var IndustryTrashFilterService
     */
    private IndustryTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  IndustrySearchService $search Handles search filtering.
     * @param  IndustrySortingService $sorting Handles sort order.
     * @param  IndustryTrashFilterService $trashFilter Handles trash filtering.
     */
    public function __construct(
        IndustrySearchService $search,
        IndustrySortingService $sorting,
        IndustryTrashFilterService $trashFilter,
    ) {
        $this->search = $search;
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of companies with search, sorting,
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
        $query = Industry::with(
            'companies',
        );

        $this->search->applySearch($query, $request);
        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $this->paginate($query, $request);

        return array_merge(
            $paginator,
            ['permissions' => $this->getPermissions()]
        );
    }

    /**
     * Return a single industry with related data loaded.
     *
     * @param  Industry $industry The route-model-bound industry instance.
     *
     * @return array
     */
    public function show(Industry $industry): array
    {
        $industry->load(
            'companies',
        );

        return $this->formatIndustry($industry);
    }

    /**
     * Paginate and transform the industry query.
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
                Industry $industry
            ): array => $this->formatIndustry($industry))
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
            'create' => Gate::allows('create', Industry::class),
            'viewAny' => Gate::allows('viewAny', Industry::class),
        ];
    }

    /**
     * Format a industry into a structured array.
     *
     * Combines core attributes, derived flags, relationships, and permissions.
     *
     * @param  Industry $industry
     *
     * @return array
     */
    private function formatIndustry(Industry $industry): array
    {
        return array_merge(
            $this->baseData($industry),
            $this->derivedData($industry),
            $this->relationshipData($industry),
            $this->permissionData($industry),
        );
    }

    /**
     * Extract core industry attributes.
     *
     * @param  Industry $industry
     *
     * @return array
     */
    private function baseData(Industry $industry): array
    {
        return [
            'id' => $industry->id,
            'name' => $industry->name,
        ];
    }

    /**
     * Extract derived flags for the industry.
     *
     * @param  Industry $industry
     *
     * @return array
     */
    private function derivedData(Industry $industry): array
    {
        return [
            'company' => $industry->company,
        ];
    }

    /**
     * Extract related contact and creator data for the industry.
     *
     * @param  Industry $industry
     *
     * @return array
     */
    private function relationshipData(Industry $industry): array
    {
        return [
            'creator' => $industry->creator,
        ];
    }

    /**
     * Determine authorisation permissions for the industry.
     *
     * @param  Industry $industry
     *
     * @return array
     */
    private function permissionData(Industry $industry): array
    {
        return [
            'permissions' => [
                'view' => Gate::allows('view', $industry),
                'update' => Gate::allows('update', $industry),
                'delete' => Gate::allows('delete', $industry),
            ],
        ];
    }
}
