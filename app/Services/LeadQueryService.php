<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class LeadQueryService
{
    private LeadSeachService $search;
    private LeadSortingService $sorting;
    private TrashFilterService $trashFilter;
    public function __construct(
        LeadSeachService $search,
        LeadSortingService $sorting,
        TrashFilterService $trashFilter,
    ) {
        $this->search = $search;
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated lead, applying filters/sorting.
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

        $query = Lead::with(
            'owner',
            'assignedTo',
        );

        $this->search->applySearch($query, $request);
        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single invoice item.
     *
     * @param Lead $lead
     *
     * @return Lead
     */
    public function show(Lead $lead): Lead
    {
        return $lead->load(
            'owner',
            'assignedTo',
        );
    }
}
