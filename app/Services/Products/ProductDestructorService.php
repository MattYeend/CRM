<?php

namespace App\Services\Products;

use App\Models\Product;

class ProductDestructorService
{
    /**
     * Soft-delete a product.
     *
     * @param Product $product
     *
     * @return void
     */
    public function destroy(Product $product): void
    {
        $userId = auth()->id();

        $product->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);

        $product->delete();
    }

    /**
     * Restore a trashed product.
     *
     * @param int $id
     *
     * @return Product
     */
    public function restore(int $id): Product
    {
        $userId = auth()->id();

        $product = Product::withTrashed()->findOrFail($id);

        if ($product->trashed()) {
            $product->update([
                'updated_by' => $userId,
                'updated_at' => now(),
            ]);
            $product->restore();
        }

        return $product;
    }
}
