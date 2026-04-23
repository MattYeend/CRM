<?php

namespace App\Services\PartStockMovements;

use App\Models\Part;
use App\Models\PartStockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Handles read queries for PartStockMovement records.
 *
 * Delegates sorting to dedicated sub-services and returns paginated stock
 * movement results, optionally scoped to a given part.
 */
class PartStockMovementQueryService
{
    /**
     * Service responsible for applying sort order to part stock movement
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
     * Return a paginated list of stock movements, optionally scoped to a
     * given part, with sorting applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request   $request Incoming HTTP request; may carry sort and
     * pagination params.
     * @param  Part|null $part    Optional part to scope movements to.
     *
     * @return array
     */
    public function list(Request $request, ?Part $part = null): array
    {
        $query = $part
            ? $part->stockMovements()->with('part')->getQuery()
            : PartStockMovement::query()->with('part');

        $this->sorting->applySorting($query, $request);

        $paginator = $this->paginate($query, $request);

        return array_merge(
            $paginator,
            ['permissions' => $this->getPermissions()]
        );
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
    public function show(PartStockMovement $partStockMovement): array
    {
        $partStockMovement->load('part', 'createdBy');

        return $this->formatPartStockMovement($partStockMovement);
    }

    /**
     * Paginate and transform the stock movement query.
     *
     * @param  Builder $query
     * @param  Request $request
     *
     * @return array
     */
    private function paginate(Builder $query, Request $request): array
    {
        $perPage = max(1, min((int) $request->query('per_page', 10), 100));

        return $query->paginate($perPage)
            ->appends($request->query())
            ->through(fn (
                PartStockMovement $movement
            ): array => $this->formatPartStockMovement($movement))
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
            'create' => Gate::allows('create', PartStockMovement::class),
            'viewAny' => Gate::allows('viewAny', PartStockMovement::class),
        ];
    }

    /**
     * Format a part stock movement into a structured array.
     *
     * Combines core attributes, relationships, derived values,
     * metadata, and permissions into a single array.
     *
     * @param  PartStockMovement $movement
     *
     * @return array
     */
    private function formatPartStockMovement(PartStockMovement $movement): array
    {
        return array_merge(
            $this->baseData($movement),
            $this->quantityData($movement),
            $this->derivedData($movement),
            $this->relationshipData($movement),
            $this->permissionData($movement),
        );
    }

    /**
     * Extract core movement attributes.
     *
     * @param  PartStockMovement $movement
     *
     * @return array
     */
    private function baseData(PartStockMovement $movement): array
    {
        return [
            'id' => $movement->id,
            'part_id' => $movement->part_id,
            'type' => $movement->type,
            'reference' => $movement->reference,
            'notes' => $movement->notes,
        ];
    }

    /**
     * Extract quantity-related fields.
     *
     * @param  PartStockMovement $movement
     *
     * @return array
     */
    private function quantityData(PartStockMovement $movement): array
    {
        return [
            'quantity' => $movement->quantity,
            'quantity_before' => $movement->quantity_before,
            'quantity_after' => $movement->quantity_after,
        ];
    }

    /**
     * Extract derived movement direction flags.
     *
     * @param  PartStockMovement $movement
     *
     * @return array
     */
    private function derivedData(PartStockMovement $movement): array
    {
        return [
            'is_inbound' => $movement->getIsInbound(),
            'is_outbound' => $movement->getIsOutbound(),
        ];
    }

    /**
     * Extract related model data for the movement.
     *
     * @param  PartStockMovement $movement
     *
     * @return array
     */
    private function relationshipData(PartStockMovement $movement): array
    {
        return [
            'part' => $movement->part,
            'created_by' => $movement->createdBy,
        ];
    }

    /**
     * Determine authorisation permissions for the movement.
     *
     * @param  PartStockMovement $movement
     *
     * @return array
     */
    private function permissionData(PartStockMovement $movement): array
    {
        return [
            'permissions' => [
                'view' => Gate::allows('view', $movement),
                'update' => Gate::allows('update', $movement),
                'delete' => Gate::allows('delete', $movement),
            ],
        ];
    }
}
