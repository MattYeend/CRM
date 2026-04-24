<?php

namespace App\Services\ProductStockMovement;

use App\Http\Requests\StoreProductStockMovementRequest;
use App\Models\Product;
use App\Models\ProductStockMovement;

/**
 * Orchestrates part stock movement operations.
 *
 * Acts as the single entry point for creating stock movements,
 * delegating business logic to the creator service.
 */
class ProductStockMovementManagementService
{
    /**
     * Service responsible for creating stock movements.
     *
     * @var ProductStockMovementCreatorService
     */
    private ProductStockMovementCreatorService $creator;

    /**
     * Inject dependencies.
     *
     * @param  ProductStockMovementCreatorService $creator Handles
     * stock movement creation.
     */
    public function __construct(
        ProductStockMovementCreatorService $creator
    ) {
        $this->creator = $creator;
    }

    /**
     * Create a new part stock movement.
     *
     * @param  Product $part The part whose stock is being modified.
     * @param  StoreProductStockMovementRequest $request Validated request data.
     *
     * @return ProductStockMovement The newly created stock movement.
     */
    public function store(
        Product $part,
        StoreProductStockMovementRequest $request
    ): ProductStockMovement {
        return $this->creator->create($part, $request);
    }
}
