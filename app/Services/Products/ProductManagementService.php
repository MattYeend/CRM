<?php

namespace App\Services\Products;

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
     * @param Product $product
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

    /**
     * Create quote from product.
     *
     * @param int $id
     *
     * @return Product
     */
    public function addQuotes(int $id, array $quotes): Product
    {
        return $this->creator->addQuotes($id, $quotes);
    }

    /**
     * Update quote from product.
     *
     * @param int $id
     *
     * @return Product
     */
    public function updateQuotes(int $id, array $quotes): Product
    {
        return $this->updater->updateQuotes($id, $quotes);
    }

    /**
     * Remove quote from product.
     *
     * @param int $id
     *
     * @return Product
     */
    public function removeQuote(int $id): Product
    {
        return $this->destructor->removeQuoteFromProduct($id);
    }

    /**
     * Restore quote from product.
     *
     * @param int $id
     *
     * @return Product
     */
    public function restoreQuote(int $id): Product
    {
        return $this->destructor->restoreQuoteOnProduct($id);
    }

    /**
     * Create order from product.
     *
     * @param int $id
     *
     * @return Product
     */
    public function addOrders(int $id, array $orders): Product
    {
        return $this->creator->addOrders($id, $orders);
    }

    /**
     * Update order from product.
     *
     * @param int $id
     *
     * @return Product
     */
    public function updateOrders(int $id, array $orders): Product
    {
        return $this->updater->updateOrders($id, $orders);
    }

    /**
     * Remove order from product.
     *
     * @param int $id
     *
     * @return Product
     */
    public function removeOrder(int $id): Product
    {
        return $this->destructor->removeOrderFromProduct($id);
    }

    /**
     * Remove deal from product.
     *
     * @param int $id
     *
     * @return Product
     */
    public function removeDeal(int $id): Product
    {
        return $this->destructor->removeDealFromProduct($id);
    }

    /**
     * Create deal from product.
     *
     * @param int $id
     *
     * @return Product
     */
    public function addDeals(int $id, array $deals): Product
    {
        return $this->creator->addDeals($id, $deals);
    }

    /**
     * Update deal from product.
     *
     * @param int $id
     *
     * @return Product
     */
    public function updateDeals(int $id, array $deals): Product
    {
        return $this->updater->updateDeals($id, $deals);
    }

    /**
     * Restore deal from product.
     *
     * @param int $id
     *
     * @return Product
     */
    public function restoreDeal(int $id): Product
    {
        return $this->destructor->restoreDealOnProduct($id);
    }

    /**
     * Restore order from product.
     *
     * @param int $id
     *
     * @return Product
     */
    public function restoreOrder(int $id): Product
    {
        return $this->destructor->restoreOrderOnProduct($id);
    }
}
