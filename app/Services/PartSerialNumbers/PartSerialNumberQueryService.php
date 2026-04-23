<?php

namespace App\Services\PartSerialNumbers;

use App\Models\Part;
use App\Models\PartSerialNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Handles read queries for PartSerialNumber records.
 *
 * Delegates search, sorting, and trash filtering to dedicated sub-services
 * and returns paginated serial number results, optionally scoped to a given
 * part.
 */
class PartSerialNumberQueryService
{
    /**
     * Service responsible for applying search filters to part serial number
     * queries.
     *
     * @var PartSerialNumberSearchService
     */
    private PartSerialNumberSearchService $search;

    /**
     * Service responsible for applying sort order to part serial number
     * queries.
     *
     * @var PartSerialNumberSortingService
     */
    private PartSerialNumberSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters to part serial
     * number queries.
     *
     * @var PartSerialNumberTrashFilterService
     */
    private PartSerialNumberTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  PartSerialNumberSearchService $search Handles search
     * filter application.
     * @param  PartSerialNumberSortingService $sorting Handles sort
     * order application.
     * @param  PartSerialNumberTrashFilterService $trashFilter Handles trash
     * visibility filtering.
     */
    public function __construct(
        PartSerialNumberSearchService $search,
        PartSerialNumberSortingService $sorting,
        PartSerialNumberTrashFilterService $trashFilter,
    ) {
        $this->search = $search;
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of serial numbers, optionally scoped to a given
     * part, with search, sorting, and trash filters applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry search,
     * sort, filter, and pagination params.
     * @param  Part|null $part Optional part to scope serial numbers to.
     *
     * @return array Paginated part serial number results.
     */
    public function list(Request $request, ?Part $part = null): array
    {
       $perPage = max(1, min((int) $request->query('per_page', 10), 100));

        $query = $part
            ? $part->serialNumbers()->with('part')->getQuery()
            : PartSerialNumber::query()->with('part');

        $this->search->applySearch($query, $request);
        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $query->paginate($perPage)
            ->appends($request->query())
            ->through(fn (PartSerialNumber $sn) => $this->formatPartSerialNumber($sn))
            ->toArray();

        return array_merge($paginator, [
            'permissions' => [
                'create' => Gate::allows('create', PartSerialNumber::class),
                'viewAny' => Gate::allows('viewAny', PartSerialNumber::class),
            ],
        ]);
    }

    /**
     * Return a single part serial number with all relevant relationships
     * loaded.
     *
     * @param  PartSerialNumber $serialNumber The route-model-bound serial
     * number instance.
     *
     * @return array
     */
    public function show(PartSerialNumber $serialNumber): array
    {
        $serialNumber->load('part', 'creator');

        return $this->formatPartSerialNumber($serialNumber);
    }

    /**
     * Format a part serial number into a structured array.
     *
     * Includes core attributes, related data, derived expiry state, and
     * authorisation permissions for the current user.
     *
     * @param  PartSerialNumber $serialNumber
     *
     * @return array
     */
    private function formatPartSerialNumber(PartSerialNumber $serialNumber): array
    {
        return [
            'id' => $serialNumber->id,
            'part_id' => $serialNumber->part_id,
            'part' => $serialNumber->part,
            'serial_number' => $serialNumber->serial_number,
            'status' => $serialNumber->status,
            'batch_number' => $serialNumber->batch_number,
            'manufactured_at' => $serialNumber->manufactured_at,
            'expires_at' => $serialNumber->expires_at,
            'is_expired' => $serialNumber->getIsExpired(),
            'is_expiring_soon' => $serialNumber->getIsExpiringSoon(),
            'creator' => $serialNumber->creator,
            'permissions' => [
                'view' => Gate::allows('view', $serialNumber),
                'update' => Gate::allows('update', $serialNumber),
                'delete' => Gate::allows('delete', $serialNumber),
            ],
        ];
    }
}
