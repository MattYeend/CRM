<?php

namespace App\Services\Attachments;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Handles read queries for Attachment records.
 *
 * Delegates sorting and trash filtering to dedicated sub-services and
 * returns paginated or single attachment results with the appropriate
 * relationships loaded.
 */
class AttachmentQueryService
{
    /**
     * Service responsible for applying sort order to attachment queries.
     *
     * @var AttachmentSortingService
     */
    private AttachmentSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters to attachment
     * queries.
     *
     * @var AttachmentTrashFilterService
     */
    private AttachmentTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  AttachmentSortingService $sorting Handles sort order application.
     * @param  AttachmentTrashFilterService $trashFilter Handles trash
     * visibility filtering.
     */
    public function __construct(
        AttachmentSortingService $sorting,
        AttachmentTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of attachments with sorting and trash filters
     * applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry sort, filter,
     * and pagination params.
     *
     * @return LengthAwarePaginator Paginated attachment results.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = Attachment::query();

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single attachment with the uploader relationship loaded.
     *
     * @param  Attachment $attachment The route-model-bound attachment instance.
     *
     * @return Attachment The attachment with uploader loaded.
     */
    public function show(Attachment $attachment): Attachment
    {
        return $attachment->load('uploader');
    }
}
