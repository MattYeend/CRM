<?php

namespace App\Services\OrderProducts;

use Illuminate\Database\Eloquent\Model;

class OrderProductManagementService
{
    private OrderProductCreatorService $creator;
    private OrderProductUpdaterService $updater;
    private OrderProductDestructorService $destructor;

    public function __construct(
        OrderProductCreatorService $creator,
        OrderProductUpdaterService $updater,
        OrderProductDestructorService $destructor
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Attach products to an Order.
     *
     * @param Model $parent The order model
     * @param array $items Products array with keys 'product_id', 'quantity', 'price', 'meta'
     */
    public function add(Model $parent, array $items): void
    {
        $this->creator->create($parent, $items);
    }

    /**
     * Update attached products on an Order.
     *
     * @param Model $parent The order model
     * @param array $items Products array to update
     */
    public function update(Model $parent, array $items): void
    {
        $this->updater->update($parent, $items);
    }

    /**
     * Remove a product from an Order.
     *
     * @param Model $parent The order model
     * @param int $productId The ID of the product to remove
     */
    public function remove(Model $parent, int $productId): void
    {
        $this->destructor->remove($parent, $productId);
    }

    /**
     * Restore a previously removed product on an Order.
     *
     * @param Model $parent The order model
     * @param int $productId The ID of the product to restore
     */
    public function restore(Model $parent, int $productId): void
    {
        $this->destructor->restore($parent, $productId);
    }
}
