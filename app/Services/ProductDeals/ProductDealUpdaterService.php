<?php

namespace App\Services\ProductDeals;

use App\Http\Requests\UpdateProductDealRequest;
use App\Models\ProductDeal;

class ProductDealUpdaterService
{
    /**
     * Update the product using request data.
     *
     * @param UpdateProductDealRequest $request
     *
     * @param ProductDeal $productDeal
     *
     * @return ProductDeal
     */
    public function update(
        UpdateProductDealRequest $request,
        ProductDeal $productDeal
    ): ProductDeal {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        if (isset($data['quantity']) && isset($data['unit_price'])) {
            $data['total_price'] = $data['quantity'] * $data['unit_price'];
        }

        $productDeal->update($data);

        return $productDeal->fresh();
    }
}
