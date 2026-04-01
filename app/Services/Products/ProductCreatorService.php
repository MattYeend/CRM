<?php

namespace App\Services\Products;

use App\Http\Requests\StoreProductRequest;
use App\Models\Product;

/**
 * Handles the creation of new Product records.
 *
 * Extracts validated data from the request, stamps the creator and
 * creation timestamp, and persists the new Product.
 */
class ProductCreatorService
{
    /**
     * Create a new product from the validated request data.
     *
     * Sets the created_by and created_at audit fields from the authenticated
     * user before persisting the record.
     *
     * @param  StoreProductRequest $request Validated request containing product
     * data.
     *
     * @return Product The newly created product record.
     */
    public function create(StoreProductRequest $request): Product
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;
        $data['created_at'] = now();

        return Product::create($data);
    }

    /**
     * Attach deals to a product.
     *
     * Syncs deal relationships without detaching existing ones.
     * Each deal may include quantity and price, which are used
     * to calculate the total value.
     *
     * @param  int $productId The product identifier.
     * @param  array $deals Array of deal data.
     *
     * @return Product The updated product instance.
     */
    public function addDeals(int $productId, array $deals): Product
    {
        $product = Product::findOrFail($productId);

        foreach ($deals as $deal) {
            $product->deals()->syncWithoutDetaching([
                $deal['deal_id'] => [
                    'quantity' => $deal['quantity'] ?? 1,
                    'price' => $deal['price'] ?? 0,
                    'total' => ($deal['quantity'] ?? 1) * ($deal['price'] ?? 0),
                ],
            ]);
        }

        return $product;
    }

    /**
     * Attach quotes to a product.
     *
     * Syncs quote relationships without detaching existing ones.
     * Each quote may include quantity and price, which are used
     * to calculate the total value.
     *
     * @param  int $productId The product identifier.
     * @param  array $quotes Array of quote data.
     *
     * @return Product The updated product instance.
     */
    public function addQuotes(int $productId, array $quotes): Product
    {
        $product = Product::findOrFail($productId);

        foreach ($quotes as $quote) {
            $quantity = $quote['quantity'] ?? 1;
            $price = $quote['price'] ?? 0;
            $product->quotes()->syncWithoutDetaching([
                $quote['quote_id'] => [
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $quantity * $price,
                ],
            ]);
        }

        return $product;
    }

    /**
     * Attach orders to a product.
     *
     * Syncs order relationships without detaching existing ones.
     * Each order may include quantity and price, which are used
     * to calculate the total value.
     *
     * @param  int $productId The product identifier.
     * @param  array $orders Array of order data.
     *
     * @return Product The updated product instance.
     */
    public function addOrders(int $productId, array $orders): Product
    {
        $product = Product::findOrFail($productId);

        foreach ($orders as $order) {
            $quantity = $order['quantity'] ?? 1;
            $price = $order['price'] ?? 0;
            $product->orders()->syncWithoutDetaching([
                $order['order_id'] => [
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $quantity * $price,
                ],
            ]);
        }

        return $product;
    }
}
