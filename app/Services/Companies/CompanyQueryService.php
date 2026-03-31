<?php

namespace App\Services\Companies;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Handles read queries for Company records.
 *
 * Delegates searching, sorting, and trash filtering to dedicated
 * sub-services and returns paginated or single company results with
 * the appropriate relationships loaded.
 */
class CompanyQueryService
{
    /**
     * Service responsible for applying search filters.
     *
     * @var CompanySearchService
     */
    private CompanySearchService $search;

    /**
     * Service responsible for applying sort order.
     *
     * @var CompanySortingService
     */
    private CompanySortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters.
     *
     * @var CompanyTrashFilterService
     */
    private CompanyTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  CompanySearchService $search Handles search filtering.
     * @param  CompanySortingService $sorting Handles sort order.
     * @param  CompanyTrashFilterService $trashFilter Handles trash filtering.
     */
    public function __construct(
        CompanySearchService $search,
        CompanySortingService $sorting,
        CompanyTrashFilterService $trashFilter,
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
     * @return LengthAwarePaginator Paginated company results.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = Company::query();

        $this->search->applySearch($query, $request);
        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single company with related data loaded.
     *
     * @param  Company $company The route-model-bound company instance.
     *
     * @return Company The company with relationships loaded.
     */
    public function show(Company $company): Company
    {
        return $company->load(
            'deals',
            'invoices',
            'attachments'
        );
    }
}
