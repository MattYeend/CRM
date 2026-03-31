<?php

namespace App\Services\DealProducts;

use Illuminate\Database\Eloquent\Model;

/**
 * Central service for managing product relationships on parent models.
 *
 * Delegates creation, update, removal, and restoration of product
 * relationships to the respective services, providing a unified API
 * for managing pivot data.
 */
class DealProductManagementService
{
    /**
     * Service responsible for creating new note records.
     *
     * @var DealProductCreatorService
     */
    private DealProductCreatorService $creator;

    /**
     * Service responsible for updating existing note records.
     *
     * @var DealProductUpdaterService
     */
    private DealProductUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring note records.
     *
     * @var DealProductDestructorService
     */
    private DealProductDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  DealProductCreatorService $creator Handles product attachment.
     * @param  DealProductUpdaterService $updater Handles pivot updates.
     * @param  DealProductDestructorService $destructor Handles removal
     * and restoration.
     */
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
