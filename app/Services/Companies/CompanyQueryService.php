<?php

namespace App\Services\Companies;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CompanyQueryService
{
    private CompanySearchService $search;
    private CompanySortingService $sorting;
    private CompanyTrashFilterService $trashFilter;
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
     * Return paginated companies, applying filters/sorting.
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

        $query = Company::query();

        $this->search->applySearch($query, $request);
        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single company.
     *
     * @param Company $company
     *
     * @return Company
     */
    public function show(Company $company): Company
    {
        return $company->load(
            'contacts',
            'deals',
            'invoices',
            'attachments'
        );
    }
}
