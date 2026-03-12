<?php

namespace App\Services\Products;

use App\Http\Requests\StoreProductRequest;
use App\Models\Product;

class ProductCreatorService
{
    /**
     * Create a new product from request data.
     *
     * @param StoreProductRequest $request
     *
     * @return Product
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
     * Create new deals for products
     *
     * @param int $id
     *
     * @param array $deals
     *
     * @return Product
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
     * Create new quotes for products
     *
     * @param int $id
     *
     * @param array $quotes
     *
     * @return Product
     */
    public function addQuotes(int $productId, array $quotes): Product
    {
        $product = Product::findOrFail($productId);

        foreach ($quotes as $quote) {
            $product->quotes()->syncWithoutDetaching([
                $quote['quote_id'] => [
                    'quantity' => $quote['quantity'] ?? 1,
                    'price' => $quote['price'] ?? 0,
                    'total' => ($quote['quantity'] ?? 1) * ($quote['price'] ?? 0),
                ],
            ]);
        }

        return $product;
    }

    /**
     * Create new quotes for products
     *
     * @param int $id
     *
     * @param array $orders
     *
     * @return Product
     */
    public function addOrders(int $productId, array $orders): Product
    {
        $product = Product::findOrFail($productId);

        foreach ($orders as $order) {
            $product->orders()->syncWithoutDetaching([
                $order['order_id'] => [
                    'quantity' => $order['quantity'] ?? 1,
                    'price' => $order['price'] ?? 0,
                    'total' => ($order['quantity'] ?? 1) * ($order['price'] ?? 0),
                ],
            ]);
        }

        return $product;
    }
}
