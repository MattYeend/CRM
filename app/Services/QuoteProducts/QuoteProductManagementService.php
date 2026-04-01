<?php

namespace App\Services\QuoteProducts;

use Illuminate\Database\Eloquent\Model;

class QuoteProductManagementService
{
    /**
     * Service responsible for creating new quote product records.
     *
     * @var QuoteProductCreatorService
     */
    private QuoteProductCreatorService $creator;

    /**
     * Service responsible for updating existing quote product records.
     *
     * @var QuoteProductUpdaterService
     */
    private QuoteProductUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring quote product records.
     *
     * @var QuoteProductDestructorService
     */
    private QuoteProductDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  QuoteProductCreatorService $creator Handles pivot creation.
     * @param  QuoteProductUpdaterService $updater Handles pivot updates.
     * @param  QuoteProductDestructorService $destructor Handles removal
     * and restoration.
     */
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
     * Attach product(s) to a parent model.
     *
     * Delegates to the creator service to attach products with pivot data.
     *
     * @param  Model $parent The parent model.
     * @param  array $items Array of product data.
     *
     * @return void
     */
    public function add(Model $parent, array $items): void
    {
        $this->creator->create($parent, $items);
    }

    /**
     * Update pivot data for products attached to a parent model.
     *
     * Delegates to the updater service to modify pivot data.
     *
     * @param  Model $parent The parent model.
     * @param  array $items Array of product data.
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
     * Delegates to the destructor service to detach the product.
     *
     * @param  Model $parent The parent model.
     * @param  int $productId The ID of the product to remove.
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
     * Delegates to the destructor service to restore the pivot relationship.
     *
     * @param  Model $parent The parent model.
     * @param  int $productId The ID of the product to restore.
     *
     * @return void
     */
    public function restore(Model $parent, int $productId): void
    {
        $this->destructor->restore($parent, $productId);
    }
}
