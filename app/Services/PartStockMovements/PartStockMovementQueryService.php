<?php

namespace App\Services\PartStockMovements;

use App\Models\PartStockMovement;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

/**
 * Handles read queries for PartStockMovement records.
 *
 * Delegates sorting to dedicated sub-services and returns
 * paginated serial number results scoped to a given part.
 */
class PartStockMovementQueryService
{
    /**
     * Service responsible for applying sort order to part serial number
     * queries.
     *
     * @var PartStockMovementSortingService
     */
    private PartStockMovementSortingService $sorting;

    /**
     * Inject the required services into the query service.
     *
     * @param  PartStockMovementSortingService $sorting Handles sort order
     * application.
     */
    public function __construct(
        PartStockMovementSortingService $sorting
    ) {
        $this->sorting = $sorting;
    }

    /**
     * Return a paginated list of serial numbers for the given part, with
     * sorting applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry search, sort,
     * filter, and pagination params.
     * @param  Part $part The part whose serial numbers should be listed.
     *
     * @return LengthAwarePaginator Paginated part serial number results.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(1, min((int) $request->query('per_page', 10), 100));

        $query = PartStockMovement::query();

        $this->sorting->applySorting($query, $request);

        $paginator = $query->paginate($perPage)->appends($request->query());
 
        $paginator->through(
            fn (PartStockMovement $movement) => $this->formatPartStockMovement($movement)
        );
 
        $paginator->appends([
            'permissions' => [
                'create' => Gate::allows('create', PartStockMovement::class),
                'viewAny' => Gate::allows('viewAny', PartStockMovement::class),
            ],
        ]);
 
        return $paginator;
    }

    /**
     * Return a single part stock movement with all relevant relationships
     * loaded.
     *
     * @param  PartStockMovement $partStockMovement The route-model-bound
     * part instance.
     *
     * @return array
     */
    public function show(
        PartStockMovement $partStockMovement
    ): array {
        $partStockMovement->load('part');
 
        return $this->formatPartStockMovement($partStockMovement);
    }

    /**
     * Format a part stock movement into a structured array.
     *
     * Includes core attributes, related data, derived direction flags, and
     * authorisation permissions for the current user.
     *
     * @param  PartStockMovement $movement
     *
     * @return array
     */
    private function formatPartStockMovement(PartStockMovement $movement): array
    {
        return [
            'id' => $movement->id,
            'part_id' => $movement->part_id,
            'part' => $movement->part,
            'type' => $movement->type,
            'quantity' => $movement->quantity,
            'quantity_before' => $movement->quantity_before,
            'quantity_after' => $movement->quantity_after,
            'reference' => $movement->reference,
            'notes' => $movement->notes,
            'is_inbound' => $movement->getIsInbound(),
            'is_outbound' => $movement->getIsOutbound(),
            'created_by' => $movement->createdBy,
            'created_at' => $movement->created_at,
            'updated_at' => $movement->updated_at,
            'permissions' => [
                'view' => Gate::allows('view', $movement),
                'update' => Gate::allows('update', $movement),
                'delete' => Gate::allows('delete', $movement),
            ],
        ];
    }
}
