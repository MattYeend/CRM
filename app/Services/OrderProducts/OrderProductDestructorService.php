<?php

namespace App\Services\OrderProducts;

use Illuminate\Database\Eloquent\Model;

/**
 * Handles removal and restoration of product relationships on parent models.
 *
 * Supports detaching products from a parent model and restoring pivot
 * records when soft deletes are enabled on the pivot table.
 */
class OrderProductDestructorService
{
    /**
     * Remove a product relationship from a parent model.
     *
     * Detaches the specified product from the parent model.
     *
     * @param  Model $parent The parent model.
     * @param  int $productId The ID of the product to remove.
     *
     * @return void
     */
    public function remove(Model $parent, int $productId): void
    {
        $parent->products()->detach($productId);
    }

    /**
     * Restore a previously removed product relationship.
     *
     * Attempts to locate a soft-deleted pivot record and restore it
     * if the pivot model supports soft deletes.
     *
     * @param  Model $parent The parent model.
     * @param  int $productId The ID of the product to restore.
     *
     * @return void
     */
    public function restore(Model $parent, int $productId): void
    {
        $pivot = $parent->products()
            ->withTrashed()
            ->wherePivot('product_id', $productId)
            ->first();
        if ($pivot && method_exists($pivot->pivot, 'restore')) {
            $pivot->pivot->restore();
        }
    }
}
