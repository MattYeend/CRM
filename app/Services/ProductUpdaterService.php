<?php

namespace App\Services;

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
        $data = $request->validated();

        $data['updated_by'] = $request->user()->id;

        $product->update($data);

        return $product->fresh();
    }
}
