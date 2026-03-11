<?php

namespace App\Services\Deals;

use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class DealQueryService
{
    private DealSearchService $search;
    private DealSortingService $sorting;
    private DealTrashFilterService $trashFilter;
    public function __construct(
        DealSearchService $search,
        DealSortingService $sorting,
        DealTrashFilterService $trashFilter,
    ) {
        $this->search = $search;
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated deals, applying filters/sorting.
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

        $query = Deal::with(
            'company',
            'contact',
            'owner',
            'pipeline',
            'stage',
        );

        $this->search->applySearch($query, $request);
        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single deal.
     *
     * @param Deal $deal
     *
     * @return Deal
     */
    public function show(Deal $deal): Deal
    {
        return $deal->load(
            'company',
            'contact',
            'owner',
            'pipeline',
            'stage',
            'notes',
            'tasks',
            'attachments',
        );
    }
}
