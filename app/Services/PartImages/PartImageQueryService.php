<?php

namespace App\Services\PartImages;

use App\Models\PartImage;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Handles read queries for PartImage records.
 *
 * Delegates sorting and trash filtering to dedicated sub-services and
 * returns paginated or single part image results with the part relationship
 * loaded.
 */
class PartImageQueryService
{
    /**
     * Service responsible for applying sort order to part image queries.
     *
     * @var PartImageSortingService
     */
    private PartImageSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters to part image
     * queries.
     *
     * @var PartImageTrashFilterService
     */
    private PartImageTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  PartImageSortingService $sorting Handles sort order application.
     * @param  PartImageTrashFilterService $trashFilter Handles trash visibility
     * filtering.
     */
    public function __construct(
        PartImageSortingService $sorting,
        PartImageTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of part images with sorting and trash filters
     * applied.
     *
     * Each result eager-loads the part relationship. The per_page value is
     * clamped between 1 and 100. All active query string parameters are
     * appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry sort, filter,
     * and pagination params.
     *
     * @return LengthAwarePaginator Paginated part image results.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = PartImage::with(
            'part',
        );

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single part image with the part relationship loaded.
     *
     * @param  PartImage $partImage The route-model-bound part image instance.
     *
     * @return PartImage The part image with the part relationship loaded.
     */
    public function show(PartImage $partImage): PartImage
    {
        return $partImage->load(
            'part',
        );
    }
}
