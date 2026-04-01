<?php

namespace App\Services\Products;

use App\Models\Product;

/**
 * Handles soft deletion and restoration of Product records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by, and
 * restored_at columns are always populated.
 */
class ProductDestructorService
{
    /**
     * Soft-delete a product.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the product.
     *
     * @param  Product $product The product instance to soft-delete.
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
     * Restore a soft-deleted product.
     *
     * Looks up the product including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the product. Returns the product unchanged if it is not currently
     * trashed.
     *
     * @param  int $id The primary key of the soft-deleted product.
     *
     * @return Product The restored product instance.
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
     * Detach all quotes from a product.
     *
     * @param  int $id The product identifier.
     *
     * @return Product The updated product instance.
     */
    public function removeQuoteFromProduct(int $id): Product
    {
        $product = Product::withTrashed()->findOrFail($id);

        $product->quotes()->detach();

        return $product;
    }

    /**
     * Detach all orders from a product.
     *
     * @param  int $id The product identifier.
     *
     * @return Product The updated product instance.
     */
    public function removeOrderFromProduct(int $id): Product
    {
        $product = Product::withTrashed()->findOrFail($id);

        $product->orders()->detach();

        return $product;
    }

    /**
     * Detach all deals from a product.
     *
     * @param  int $id The product identifier.
     *
     * @return Product The updated product instance.
     */
    public function removeDealFromProduct(int $id): Product
    {
        $product = Product::withTrashed()->findOrFail($id);

        $product->deals()->detach();

        return $product;
    }

    /**
     * Restore soft-deleted quote relationships on a product.
     *
     * Attempts to restore pivot records if the relationship
     * supports soft deletes.
     *
     * @param  int $id The product identifier.
     *
     * @return Product The updated product instance.
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
     * Restore soft-deleted order relationships on a product.
     *
     * @param  int $id The product identifier.
     *
     * @return Product The updated product instance.
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
     * Restore soft-deleted deal relationships on a product.
     *
     * @param  int $id The product identifier.
     *
     * @return Product The updated product instance.
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
