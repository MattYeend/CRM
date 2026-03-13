<?php

namespace App\Services\QuoteProducts;

use Illuminate\Database\Eloquent\Model;

class QuoteProductManagementService
{
    private QuoteProductCreatorService $creator;
    private QuoteProductUpdaterService $updater;
    private QuoteProductDestructorService $destructor;

    public function __construct(
        QuoteProductCreatorService $creator,
        QuoteProductUpdaterService $updater,
        QuoteProductDestructorService $destructor
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Attach products to a Quote.
     *
     * @param Model $parent The quote model
     * @param array $items Products array with keys
     * 'product_id', 'quantity', 'price', 'meta'
     */
    public function add(Model $parent, array $items): void
    {
        $this->creator->create($parent, $items);
    }

    /**
     * Update attached products on a Quote.
     *
     * @param Model $parent The quote model
     * @param array $items Products array to update
     */
    public function update(Model $parent, array $items): void
    {
        $this->updater->update($parent, $items);
    }

    /**
     * Remove a product from a Quote.
     *
     * @param Model $parent The quote model
     * @param int $productId The ID of the product to remove
     */
    public function remove(Model $parent, int $productId): void
    {
        $this->destructor->remove($parent, $productId);
    }

    /**
     * Restore a previously removed product on a Quote.
     *
     * @param Model $parent The quote model
     * @param int $productId The ID of the product to restore
     */
    public function restore(Model $parent, int $productId): void
    {
        $this->destructor->restore($parent, $productId);
    }
}
