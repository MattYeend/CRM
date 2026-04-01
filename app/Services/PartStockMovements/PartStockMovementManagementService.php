<?php

namespace App\Services\PartStockMovements;

use App\Http\Requests\StorePartStockMovementRequest;
use App\Models\Part;
use App\Models\PartStockMovement;

/**
 * Orchestrates part stock movement operations.
 *
 * Acts as the single entry point for creating stock movements,
 * delegating business logic to the creator service.
 */
class PartStockMovementManagementService
{
    /**
     * Service responsible for creating stock movements.
     *
     * @var PartStockMovementCreatorService
     */
    private PartStockMovementCreatorService $creator;

    /**
     * Inject dependencies.
     *
     * @param  PartStockMovementCreatorService $creator Handles stock movement creation.
     */
    public function __construct(
        PartStockMovementCreatorService $creator
    ) {
        $this->creator = $creator;
    }

    /**
     * Create a new part stock movement.
     *
     * @param  Part $part The part whose stock is being modified.
     * @param  StorePartStockMovementRequest $request Validated request data.
     *
     * @return PartStockMovement The newly created stock movement.
     */
    public function store(
        Part $part,
        StorePartStockMovementRequest $request
    ): PartStockMovement {
        return $this->creator->create($part, $request);
    }
}
