<?php

namespace App\Services\Companies;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
     * @return array
     */
    public function list(Request $request): array
    {
        $query = Company::with(
            'deals',
            'industry',
            'invoices',
            'attachments'
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
     * Return a single company with related data loaded.
     *
     * @param  Company $company The route-model-bound company instance.
     *
     * @return array
     */
    public function show(Company $company): array
    {
        $company->load(
            'deals',
            'industry',
            'invoices',
            'attachments',
        );

        return $this->formatCompany($company);
    }

    /**
     * Paginate and transform the company query.
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
                Company $company
            ): array => $this->formatCompany($company))
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
            'create' => Gate::allows('create', Company::class),
            'viewAny' => Gate::allows('viewAny', Company::class),
        ];
    }

    /**
     * Format a company into a structured array.
     *
     * Combines core attributes, derived flags, relationships, and permissions.
     *
     * @param  Company $company
     *
     * @return array
     */
    private function formatCompany(Company $company): array
    {
        return array_merge(
            $this->baseData($company),
            $this->derivedData($company),
            $this->relationshipData($company),
            $this->permissionData($company),
        );
    }

    /**
     * Extract core company attributes.
     *
     * @param  Company $company
     *
     * @return array
     */
    private function baseData(Company $company): array
    {
        return [
            'id' => $company->id,
            'name' => $company->name,
            'industry' => $company->industry?->name,
            'industry_id' => $company->industry_id,
            'website' => $company->website,
            'website_host' => $company->website_host,
            'phone' => $company->phone,
            'address' => $company->address,
            'city' => $company->city,
            'region' => $company->region,
            'postal_code' => $company->postal_code,
            'country' => $company->country,
            'full_address' => $company->full_address,
            'meta' => $company->meta,
        ];
    }

    /**
     * Extract derived flags for the company.
     *
     * @param  Company $company
     *
     * @return array
     */
    private function derivedData(Company $company): array
    {
        return [
            'has_deals' => $company->has_deals,
            'has_outstanding_invoices' => $company->has_outstanding_invoices,
            'attachments' => $company->attachments,
        ];
    }

    /**
     * Extract related contact and creator data for the company.
     *
     * @param  Company $company
     *
     * @return array
     */
    private function relationshipData(Company $company): array
    {
        return [
            'contact_first_name' => $company->contact_first_name,
            'contact_last_name' => $company->contact_last_name,
            'contact_full_name' => $company->contact_full_name,
            'contact_email' => $company->contact_email,
            'contact_phone' => $company->contact_phone,
            'creator' => $company->creator,
        ];
    }

    /**
     * Determine authorisation permissions for the company.
     *
     * @param  Company $company
     *
     * @return array
     */
    private function permissionData(Company $company): array
    {
        return [
            'permissions' => [
                'view' => Gate::allows('view', $company),
                'update' => Gate::allows('update', $company),
                'delete' => Gate::allows('delete', $company),
            ],
        ];
    }
}
