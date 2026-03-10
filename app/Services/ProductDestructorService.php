<?php

namespace App\Services;

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
        $product->update([
            'deleted_by' => auth()->id(),
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
        $product = Product::withTrashed()->findOrFail($id);

        if ($product->trashed()) {
            $product->restore();
        }

        return $product;
    }
}
