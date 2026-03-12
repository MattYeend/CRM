<?php

namespace App\Services\QuoteProducts;

use Illuminate\Database\Eloquent\Model;

class QuoteProductDestructorService
{
    /**
     * Remove a product from a quote.
     *
     * @param Model $quote The quote model
     * @param int $productId The ID of the product to remove
     *
     * @return void
     */
    public function remove(Model $quote, int $productId): void
    {
        $quote->products()->detach($productId);
    }

    /**
     * Restore a previously removed product from a quote.
     *
     * Works only if the pivot uses soft deletes.
     *
     * @param Model $quote The quote model
     * @param int $productId The ID of the product to restore
     *
     * @return void
     */
    public function restore(Model $quote, int $productId): void
    {
        $pivot = $quote->products()->withTrashed()->wherePivot('product_id', $productId)->first();
        if ($pivot && method_exists($pivot->pivot, 'restore')) {
            $pivot->pivot->restore();
        }
    }
}
