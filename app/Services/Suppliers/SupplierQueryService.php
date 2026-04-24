<?php

namespace App\Services\Suppliers;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Handles read queries for Supplier records.
 *
 * Delegates searching, sorting, and trash filtering to dedicated
 * sub-services and returns paginated or single supplier results with
 * the appropriate relationships loaded.
 */
class SupplierQueryService
{
    /**
     * Service responsible for applying the search.
     *
     * @var SupplierSearchService
     */
    private SupplierSearchService $search;

    /**
     * Service responsible for applying sort order.
     *
     * @var SupplierSortingService
     */
    private SupplierSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters.
     *
     * @var SupplierTrashFilterService
     */
    private SupplierTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  SupplierSearchService $search Handles search filtering.
     * @param  SupplierSortingService $sorting Handles sort order.
     * @param  SupplierTrashFilterService $trashFilter Handles
     * trash filtering.
     */
    public function __construct(
        SupplierSearchService $search,
        SupplierSortingService $sorting,
        SupplierTrashFilterService $trashFilter,
    ) {
        $this->search = $search;
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of suppliers with search, sorting,
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
        $query = Supplier::with(
            'parts',
            'partSuppliers',
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
     * Return a single supplier with related data loaded.
     *
     * @param Supplier $supplier The route-model bound supplier
     * instance.
     *
     * @return array
     */
    public function show(Supplier $supplier): array
    {
        $supplier->load(
            'parts',
            'partSuppliers',
        );

        return $this->formatSupplier($supplier);
    }

    /**
     * Paginate and transform the supplier query.
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
                Supplier $supplier
            ): array => $this->formatSupplier($supplier))
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
            'create' => Gate::allows('create', Supplier::class),
            'viewAny' => Gate::allows('viewAny', Supplier::class),
        ];
    }

    /**
     * Format a supplier into a structured array.
     *
     * Includes core attributes, contact and address data, derived accessors,
     * related parts data, and authorisation permissions for the current user.
     *
     * @param  Supplier $supplier
     *
     * @return array
     */
    private function formatSupplier(Supplier $supplier): array
    {
        return array_merge(
            $this->baseData($supplier),
            $this->addressData($supplier),
            $this->contactData($supplier),
            $this->relationshipData($supplier),
            $this->permissionData($supplier),
        );
    }

    /**
     * Extract base supplier attributes.
     *
     * Includes core identifying and financial fields.
     *
     * @param  Supplier $supplier
     *
     * @return array
     */
    private function baseData(Supplier $supplier): array
    {
        return [
            'id' => $supplier->id,
            'name' => $supplier->name,
            'code' => $supplier->code,
            'email' => $supplier->email,
            'phone' => $supplier->phone,
            'website' => $supplier->website,
            'website_host' => $supplier->getWebsiteHostAttribute(),
            'currency' => $supplier->currency,
            'payment_terms' => $supplier->payment_terms,
            'tax_number' => $supplier->tax_number,
            'is_active' => $supplier->is_active,
            'notes' => $supplier->notes,
        ];
    }

    /**
     * Extract supplier address-related data.
     *
     * Includes individual address fields and the computed full address.
     *
     * @param  Supplier $supplier
     *
     * @return array
     */
    private function addressData(Supplier $supplier): array
    {
        return [
            'address_line_1' => $supplier->address_line_1,
            'address_line_2' => $supplier->address_line_2,
            'city' => $supplier->city,
            'county' => $supplier->county,
            'postcode' => $supplier->postcode,
            'country' => $supplier->country,
            'full_address' => $supplier->getFullAddressAttribute(),
        ];
    }

    /**
     * Extract supplier contact information.
     *
     * Includes primary contact name, email, and phone number.
     *
     * @param  Supplier $supplier
     *
     * @return array
     */
    private function contactData(Supplier $supplier): array
    {
        return [
            'contact_name' => $supplier->contact_name,
            'contact_email' => $supplier->contact_email,
            'contact_phone' => $supplier->contact_phone,
        ];
    }

    /**
     * Extract supplier relationship data.
     *
     * Includes related parts, part-supplier links, and creator.
     *
     * @param  Supplier $supplier
     *
     * @return array
     */
    private function relationshipData(Supplier $supplier): array
    {
        return [
            'parts' => $supplier->parts,
            'part_suppliers' => $supplier->partSuppliers,
            'creator' => $supplier->creator,
        ];
    }

    /**
     * Determine authorisation permissions for the supplier.
     *
     * Evaluates the current user's ability to view, update,
     * and delete the supplier.
     *
     * @param  Supplier $supplier
     *
     * @return array
     */
    private function permissionData(Supplier $supplier): array
    {
        return [
            'permissions' => [
                'view' => Gate::allows('view', $supplier),
                'update' => Gate::allows('update', $supplier),
                'delete' => Gate::allows('delete', $supplier),
            ],
        ];
    }
}
