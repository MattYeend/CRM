<?php

namespace App\Services\Products;

use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;

class ProductUpdaterService
{
    /**
     * Update the product using request data.
     *
     * @param UpdateProductRequest $request
     *
     * @param Product $product
     *
     * @return Product
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
     * Update deals for products
     *
     * @param int $id
     *
     * @param array $deals
     *
     * @return Product
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
     * Update quotes for products
     *
     * @param int $id
     *
     * @param array $quotes
     *
     * @return Product
     */
    public function updateQuotes(int $productId, array $quotes): Product
    {
        $product = Product::findOrFail($productId);

        foreach ($quotes as $quote) {
            $product->quotes()->updateExistingPivot($quote['quote_id'], [
                'quantity' => $quote['quantity'] ?? 1,
                'price' => $quote['price'] ?? 0,
                'total' => ($quote['quantity'] ?? 1) * ($quote['price'] ?? 0),
            ]);
        }

        return $product;
    }

    /**
     * Update orders for products
     *
     * @param int $id
     *
     * @param array $orders
     *
     * @return Product
     */
    public function updateOrders(int $productId, array $orders): Product
    {
        $product = Product::findOrFail($productId);

        foreach ($orders as $order) {
            $product->orders()->updateExistingPivot($order['order_id'], [
                'quantity' => $order['quantity'] ?? 1,
                'price' => $order['price'] ?? 0,
                'total' => ($order['quantity'] ?? 1) * ($order['price'] ?? 0),
            ]);
        }

        return $product;
    }
}
