<?php

namespace App\Services;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;

class ProductManagementService
{
    private ProductCreatorService $creator;
    private ProductUpdaterService $updater;
    private ProductDestructorService $destructor;

    public function __construct(
        ProductCreatorService $creator,
        ProductUpdaterService $updater,
        ProductDestructorService $destructor,
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new product.
     *
     * @param StoreProductRequest $request
     *
     * @return Product
     */
    public function store(StoreProductRequest $request): Product
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing product.
     *
     * @param UpdateProductRequest $request
     *
     * @param Product $pipelineStage
     *
     * @return Product
     */
    public function update(
        UpdateProductRequest $request,
        Product $product
    ): Product {
        return $this->updater->update($request, $product);
    }

    /**
     * Delete a product (soft delete).
     *
     * @param Product $product
     *
     * @return void
     */
    public function destroy(Product $product): void
    {
        $this->destructor->destroy($product);
    }

    /**
     * Restore a soft-deleted product.
     *
     * @param int $id
     *
     * @return Product
     */
    public function restore(int $id): Product
    {
        return $this->destructor->restore($id);
    }
}
