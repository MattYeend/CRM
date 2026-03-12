<?php

namespace App\Services\ProductDeals;

use App\Models\ProductDeal;

class ProductDealDestructorService
{
    /**
     * Soft-delete a product.
     *
     * @param ProductDeal $productDeal
     *
     * @return void
     */
    public function destroy(ProductDeal $productDeal): void
    {
        $userId = auth()->id();

        $productDeal->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);

        $productDeal->delete();
    }

    /**
     * Restore a trashed product deal.
     *
     * @param int $id
     *
     * @return ProductDeal
     */
    public function restore(int $id): ProductDeal
    {
        $userId = auth()->id();

        $productDeal = ProductDeal::withTrashed()->findOrFail($id);

        if ($productDeal->trashed()) {
            $productDeal->update([
                'updated_by' => $userId,
                'updated_at' => now(),
            ]);
            $productDeal->restore();
        }

        return $productDeal;
    }
}
