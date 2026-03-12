<?php

namespace App\Services\Quotes;

use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class QuoteQueryService
{
    private QuoteSortingService $sorting;
    private QuoteTrashFilterService $trashFilter;
    public function __construct(
        QuoteSortingService $sorting,
        QuoteTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated task, applying filters/sorting.
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

        $query = Quote::with('creator', 'updater', 'deal');

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single quote.
     *
     * @param Quote $quote
     *
     * @return Quote
     */
    public function show(Quote $quote): Quote
    {
        return $quote->load('creator', 'updater', 'deal');
    }
}
