<?php

namespace App\Services;

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

        return Product::create($data);
    }
}
