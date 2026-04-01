<?php

namespace App\Services\Products;

use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;

/**
 * Handles updates to Product records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * updates to the product.
 */
class ProductUpdaterService
{
    /**
     * Update an existing product.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user and timestamp to audit fields, updates the product, and returns
     * a fresh instance.
     *
     * @param  UpdateProductRequest $request The request containing
     * validated product data.
     * @param  Product $product The product to update.
     *
     * @return Product The updated product instance.
     */
    public function update(
        UpdateProductRequest $request,
        Product $product
    ): Product {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        $product->update($data);

        return $product->fresh();
    }

    /**
     * Update deal relationships on a product.
     *
     * Updates existing pivot records for deals associated with the product.
     * Each deal may include quantity and price, which are used to recalculate
     * the total value.
     *
     * @param  int $productId The product identifier.
     * @param  array $deals Array of deal data.
     *
     * @return Product The updated product instance.
     */
    public function updateDeals(int $productId, array $deals): Product
    {
        $product = Product::findOrFail($productId);

        foreach ($deals as $deal) {
            $product->deals()->updateExistingPivot($deal['deal_id'], [
                'quantity' => $deal['quantity'] ?? 1,
                'price' => $deal['price'] ?? 0,
                'total' => ($deal['quantity'] ?? 1) * ($deal['price'] ?? 0),
            ]);
        }

        return $product;
    }

    /**
     * Update quote relationships on a product.
     *
     * Updates existing pivot records for quotes associated with the product.
     *
     * @param  int   $productId The product identifier.
     * @param  array $quotes    Array of updated quote data.
     *
     * @return Product The updated product instance.
     */
    public function updateQuotes(int $productId, array $quotes): Product
    {
        $product = Product::findOrFail($productId);

        foreach ($quotes as $quote) {
            $quantity = $quote['quantity'] ?? 1;
            $price = $quote['price'] ?? 0;
            $product->quotes()->updateExistingPivot($quote['quote_id'], [
                'quantity' => $quantity,
                'price' => $price,
                'total' => $quantity * $price,
            ]);
        }

        return $product;
    }

    /**
     * Update order relationships on a product.
     *
     * Updates existing pivot records for orders associated with the product.
     *
     * @param  int   $productId The product identifier.
     * @param  array $orders    Array of updated order data.
     *
     * @return Product The updated product instance.
     */
    public function updateOrders(int $productId, array $orders): Product
    {
        $product = Product::findOrFail($productId);

        foreach ($orders as $order) {
            $quantity = $order['quantity'] ?? 1;
            $price = $order['price'] ?? 0;
            $product->orders()->updateExistingPivot($order['order_id'], [
                'quantity' => $quantity,
                'price' => $price,
                'total' => $quantity * $price,
            ]);
        }

        return $product;
    }
}
