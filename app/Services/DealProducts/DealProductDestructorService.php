<?php

namespace App\Services\DealProducts;

use Illuminate\Database\Eloquent\Model;

class DealProductDestructorService
{
    /**
     * Remove pivot relation
     *
     * @param Model $parent
     *
     * @param int $productId
     */
    public function remove(Model $parent, int $productId): void
    {
        $parent->products()->detach($productId);
    }

    /**
     * Restore pivot relation (if using soft deletes on pivot)
     *
     * @param Model $parent
     *
     * @param int $productId
     */
    public function restore(Model $parent, int $productId): void
    {
        $pivot = $parent->products()->withTrashed()->wherePivot('product_id', $productId)->first();
        if ($pivot && method_exists($pivot->pivot, 'restore')) {
            $pivot->pivot->restore();
        }
    }
}
