<?php

namespace App\Services\ProductDeals;

use App\Http\Requests\StoreProductDealRequest;
use App\Http\Requests\UpdateProductDealRequest;
use App\Models\ProductDeal;

class ProductDealManagementService
{
    private ProductDealCreatorService $creator;
    private ProductDealUpdaterService $updater;
    private ProductDealDestructorService $destructor;

    public function __construct(
        ProductDealCreatorService $creator,
        ProductDealUpdaterService $updater,
        ProductDealDestructorService $destructor,
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new product deal.
     *
     * @param StoreProductDealRequest $request
     *
     * @return ProductDeal
     */
    public function store(StoreProductDealRequest $request): ProductDeal
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing product deal.
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
        return $this->updater->update($request, $productDeal);
    }

    /**
     * Delete a product deal (soft delete).
     *
     * @param ProductDeal $productDeal
     *
     * @return void
     */
    public function destroy(ProductDeal $productDeal): void
    {
        $this->destructor->destroy($productDeal);
    }

    /**
     * Restore a soft-deleted product deal.
     *
     * @param int $id
     *
     * @return ProductDeal
     */
    public function restore(int $id): ProductDeal
    {
        return $this->destructor->restore($id);
    }
}
