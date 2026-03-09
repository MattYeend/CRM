<?php

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AttachmentQueryService
{
    private AttachmentSortingService $sorting;
    private TrashFilterService $trashFilter;
    public function __construct(
        AttachmentSortingService $sorting,
        TrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated attachment with roles, applying filters/sorting.
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

        $query = Attachment::query();

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single attachment with roles loaded.
     *
     * @param Attachment $attachment
     *
     * @return Attachment
     */
    public function show(Attachment $attachment): Attachment
    {
        return $attachment->load('uploader');
    }
}
