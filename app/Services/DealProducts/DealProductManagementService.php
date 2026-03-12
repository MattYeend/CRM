<?php

namespace App\Services\DealProducts;

use Illuminate\Database\Eloquent\Model;

class DealProductManagementService
{
    private DealProductCreatorService $creator;
    private DealProductUpdaterService $updater;
    private DealProductDestructorService $destructor;

    public function __construct(
        DealProductCreatorService $creator,
        DealProductUpdaterService $updater,
        DealProductDestructorService $destructor,
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Attach product(s) to a parent model (Deal, Order, Quote).
     *
     * @param Model $parent The parent model to attach products to.
     *
     * @param array $items Array of products with keys:
     *                     - 'product_id' (int)
     *                     - 'quantity' (int)
     *                     - 'price' (float)
     *                     - 'meta' (array|null)
     *
     * @return void
     */
    public function add(Model $parent, array $items): void
    {
        $this->creator->create($parent, $items);
    }

    /**
     * Update pivot information for products attached to a parent model.
     *
     * @param Model $parent The parent model.
     *
     * @param array $items Array of products to update.
     *
     * @return void
     */
    public function update(Model $parent, array $items): void
    {
        $this->updater->update($parent, $items);
    }

    /**
     * Remove a product from a parent model.
     *
     * @param Model $parent The parent model.
     * @param int $productId The ID of the product to remove.
     *
     * @return void
     */
    public function remove(Model $parent, int $productId): void
    {
        $this->destructor->remove($parent, $productId);
    }

    /**
     * Restore a previously removed product on a parent model.
     *
     * @param Model $parent The parent model.
     * @param int $productId The ID of the product to restore.
     *
     * @return void
     */
    public function restore(Model $parent, int $productId): void
    {
        $this->destructor->restore($parent, $productId);
    }
}
