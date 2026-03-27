<?php

namespace App\Services\PartStockMovements;

use App\Http\Requests\StorePartStockMovementRequest;
use App\Models\Part;
use App\Models\PartStockMovement;

class PartStockMovementManagementService
{
    private PartStockMovementCreatorService $creator;

    public function __construct(
        PartStockMovementCreatorService $creator,
    ) {
        $this->creator = $creator;
    }

    /**
     * Create a new part stock movement.
     *
     * @param StorePartStockMovementRequest $request
     *
     * @return PartStockMovement
     */
    public function store(
        Part $part,
        StorePartStockMovementRequest $request
    ): PartStockMovement {
        return $this->creator->create($part, $request);
    }
}
