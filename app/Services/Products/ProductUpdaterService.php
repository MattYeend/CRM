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
}
