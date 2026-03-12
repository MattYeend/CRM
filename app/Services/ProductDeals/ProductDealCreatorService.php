<?php

namespace App\Services\ProductDeals;

use App\Http\Requests\StoreProductDealRequest;
use App\Models\ProductDeal;

class ProductDealCreatorService
{
    /**
     * Create a new product deal from request data.
     *
     * @param StoreProductDealRequest $request
     *
     * @return ProductDeal
     */
    public function create(StoreProductDealRequest $request): ProductDeal
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;
        $data['created_at'] = now();

        if (isset($data['quantity']) && isset($data['unit_price'])) {
            $data['total_price'] = $data['quantity'] * $data['unit_price'];
        }

        return ProductDeal::create($data);
    }
}
