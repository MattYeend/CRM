<?php

namespace App\Services\DealProducts;

use App\Models\DealProduct;
use Illuminate\Database\Eloquent\Model;

/**
 * Handles removal and restoration of product relationships on parent models.
 *
 * The deal_products pivot table uses soft deletes, so removal is handled
 * by soft-deleting the pivot record rather than detaching it. This preserves
 * the relationship history and allows restoration.
 */
class DealProductDestructorService
{
    /**
     * Remove a product relationship from a parent model.
     *
     * Soft-deletes the pivot record rather than detaching it, so the
     * relationship can be restored and the audit trail is preserved.
     *
     * @param  Model $parent The parent model.
     * @param  int $productId The ID of the product to remove.
     *
     * @return void
     */
    public function remove(Model $parent, int $productId): void
    {
        DealProduct::where('deal_id', $parent->id)
            ->where('product_id', $productId)
            ->whereNull('deleted_at')
            ->first()
            ?->delete();
    }

    /**
     * Restore a previously removed product relationship.
     *
     * Locates the soft-deleted pivot record and restores it.
     *
     * @param  Model $parent The parent model.
     * @param  int $productId The ID of the product to restore.
     *
     * @return void
     */
    public function restore(Model $parent, int $productId): void
    {
        DealProduct::where('deal_id', $parent->id)
            ->where('product_id', $productId)
            ->onlyTrashed()
            ->first()
            ?->restore();
    }
}
