<?php

namespace App\Services\Suppliers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
     * string pameters are appended to the paginator links.
     *
     * @param Request $request Incoming HTTP request; may carry search,
     * sorting, and trash filter param.
     *
     * @return LengthAwarePaginator Paginated suppliers item results.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = Supplier::with(
            'parts',
            'partSuppliers',
        );

        $this->search->applySearch($query, $request);
        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $query->paginate($perPage)->appends($request->query());

        $paginator->through(
            fn (Supplier $supplier) => $this->formatSupplier($supplier)
        );

        $paginator->appends([
            'permissions' => [
                'create' => Gate::allows('create', Supplier::class),
                'viewAny' => Gate::allows('viewAny', Supplier::class),
            ],
        ]);

        return $paginator;
    }

    /**
     * Return a single supplier with related date loaded.
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
        return [
            'id' => $supplier->id,
            'name' => $supplier->name,
            'code' => $supplier->code,
            'email' => $supplier->email,
            'phone' => $supplier->phone,
            'website' => $supplier->website,
            'website_host' => $supplier->getWebsiteHostAttribute(),
            'address_line_1' => $supplier->address_line_1,
            'address_line_2' => $supplier->address_line_2,
            'city' => $supplier->city,
            'county' => $supplier->county,
            'postcode' => $supplier->postcode,
            'country' => $supplier->country,
            'full_address' => $supplier->getFullAddressAttribute(),
            'currency' => $supplier->currency,
            'payment_terms' => $supplier->payment_terms,
            'tax_number' => $supplier->tax_number,
            'contact_name' => $supplier->contact_name,
            'contact_email' => $supplier->contact_email,
            'contact_phone' => $supplier->contact_phone,
            'is_active' => $supplier->is_active,
            'notes' => $supplier->notes,
            'is_test' => $supplier->is_test,
            'parts' => $supplier->parts,
            'part_suppliers' => $supplier->partSuppliers,
            'creator' => $supplier->creator,
            'created_at' => $supplier->created_at,
            'updated_at' => $supplier->updated_at,
            'deleted_at' => $supplier->deleted_at,
            'permissions' => [
                'view' => Gate::allows('view', $supplier),
                'update' => Gate::allows('update', $supplier),
                'delete' => Gate::allows('delete', $supplier),
            ],
        ];
    }
}
