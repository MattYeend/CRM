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

        $product->deals()->detach();
        $product->quotes()->detach();
        $product->orders()->detach();
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

    /**
     * Detach quote from product.
     *
     * @param int $id
     *
     * @return Product
     */
    public function removeQuoteFromProduct(int $id): Product
    {
        $product = Product::withTrashed()->findOrFail($id);

        $product->quotes()->detach();

        return $product;
    }

    /**
     * Detach quote from product.
     *
     * @param int $id
     *
     * @return Product
     */
    public function removeOrderFromProduct(int $id): Product
    {
        $product = Product::withTrashed()->findOrFail($id);

        $product->orders()->detach();

        return $product;
    }

    /**
     * Detach quote from product.
     *
     * @param int $id
     *
     * @return Product
     */
    public function removeDealFromProduct(int $id): Product
    {
        $product = Product::withTrashed()->findOrFail($id);

        $product->deals()->detach();

        return $product;
    }

    /**
     * Restore previously removed quote on product (pivot).
     *
     * @param int $id
     *
     * @return Product
     */
    public function restoreQuoteOnProduct(int $id): Product
    {
        $product = Product::withTrashed()->findOrFail($id);

        $product->quotes()->withTrashed()->get()->each(function ($pivot) {
            if (method_exists($pivot->pivot, 'restore')) {
                $pivot->pivot->restore();
            }
        });

        return $product;
    }

    /**
     * Restore previously removed order on product (pivot).
     *
     * @param int $id
     *
     * @return Product
     */
    public function restoreOrderOnProduct(int $id): Product
    {
        $product = Product::withTrashed()->findOrFail($id);

        $product->orders()->withTrashed()->get()->each(function ($pivot) {
            if (method_exists($pivot->pivot, 'restore')) {
                $pivot->pivot->restore();
            }
        });

        return $product;
    }

    /**
     * Restore previously removed deal on product (pivot).
     *
     * @param int $id
     *
     * @return Product
     */
    public function restoreDealOnProduct(int $id): Product
    {
        $product = Product::withTrashed()->findOrFail($id);

        $product->deals()->withTrashed()->get()->each(function ($pivot) {
            if (method_exists($pivot->pivot, 'restore')) {
                $pivot->pivot->restore();
            }
        });

        return $product;
    }
}
