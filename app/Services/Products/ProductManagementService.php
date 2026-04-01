<?php

namespace App\Services\Products;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;

/**
 * Orchestrates product lifecycle operations by delegating to focused
 * sub-services.
 *
 * Acts as the single entry point for product create, update, delete, and
 * restore operations, keeping controllers decoupled from the underlying
 * service implementations.
 */
class ProductManagementService
{
    /**
     * Service responsible for creating new product records.
     *
     * @var ProductCreatorService
     */
    private ProductCreatorService $creator;

    /**
     * Service responsible for updating existing product records.
     *
     * @var ProductUpdaterService
     */
    private ProductUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring product records.
     *
     * @var ProductDestructorService
     */
    private ProductDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  ProductCreatorService $creator Handles product creation.
     * @param  ProductUpdaterService $updater Handles product updates.
     * @param  ProductDestructorService $destructor Handles product deletion
     * and restoration.
     */
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
     * @param  StoreProductRequest $request Validated request containing product
     * data.
     *
     * @return Product The newly created product.
     */
    public function store(StoreProductRequest $request): Product
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing product.
     *
     * @param  UpdateProductRequest $request Validated request containing
     * updated product data.
     * @param  Product $product The product instance to update.
     *
     * @return Product The updated product.
     */
    public function update(
        UpdateProductRequest $request,
        Product $product
    ): Product {
        return $this->updater->update($request, $product);
    }

    /**
     * Soft-delete a product.
     *
     * Delegates to the destructor service to perform a soft-delete.
     *
     * @param  Product $product The product to delete.
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
     * @param  int $id The primary key of the soft-deleted product.
     *
     * @return Product The restored product.
     */
    public function restore(int $id): Product
    {
        return $this->destructor->restore($id);
    }

    /**
     * Attach quotes to a product.
     *
     * Delegates to the creator service to sync quote relationships
     * without detaching existing ones.
     *
     * @param  int $id The product identifier.
     * @param  array $quotes Array of quote data.
     *
     * @return Product The updated product instance.
     */
    public function addQuotes(int $id, array $quotes): Product
    {
        return $this->creator->addQuotes($id, $quotes);
    }

    /**
     * Update quotes on a product.
     *
     * Delegates to the updater service to modify existing
     * quote relationships.
     *
     * @param  int $id The product identifier.
     * @param  array $quotes Array of updated quote data.
     *
     * @return Product The updated product instance.
     */
    public function updateQuotes(int $id, array $quotes): Product
    {
        return $this->updater->updateQuotes($id, $quotes);
    }

    /**
     * Detach all quotes from a product.
     *
     * @param  int $id The product identifier.
     *
     * @return Product The updated product instance.
     */
    public function removeQuote(int $id): Product
    {
        return $this->destructor->removeQuoteFromProduct($id);
    }

    /**
     * Restore soft-deleted quote relationships on a product.
     *
     * @param  int $id The product identifier.
     *
     * @return Product The updated product instance.
     */
    public function restoreQuote(int $id): Product
    {
        return $this->destructor->restoreQuoteOnProduct($id);
    }

    /**
     * Attach orders to a product.
     *
     * Delegates to the creator service to sync order relationships
     * without detaching existing ones.
     *
     * @param  int $id The product identifier.
     * @param  array $orders Array of order data.
     *
     * @return Product The updated product instance.
     */
    public function addOrders(int $id, array $orders): Product
    {
        return $this->creator->addOrders($id, $orders);
    }

    /**
     * Update orders on a product.
     *
     * @param  int $id The product identifier.
     * @param  array $orders Array of updated order data.
     *
     * @return Product The updated product instance.
     */
    public function updateOrders(int $id, array $orders): Product
    {
        return $this->updater->updateOrders($id, $orders);
    }

    /**
     * Detach all orders from a product.
     *
     * @param  int $id The product identifier.
     *
     * @return Product The updated product instance.
     */
    public function removeOrder(int $id): Product
    {
        return $this->destructor->removeOrderFromProduct($id);
    }

    /**
     * Restore soft-deleted order relationships on a product.
     *
     * @param  int $id The product identifier.
     *
     * @return Product The updated product instance.
     */
    public function restoreOrder(int $id): Product
    {
        return $this->destructor->restoreOrderOnProduct($id);
    }

    /**
     * Attach deals to a product.
     *
     * @param  int $id The product identifier.
     * @param  array $deals Array of deal data.
     *
     * @return Product The updated product instance.
     */
    public function addDeals(int $id, array $deals): Product
    {
        return $this->creator->addDeals($id, $deals);
    }

    /**
     * Update deals on a product.
     *
     * @param  int $id The product identifier.
     * @param  array $deals Array of updated deal data.
     *
     * @return Product The updated product instance.
     */
    public function updateDeals(int $id, array $deals): Product
    {
        return $this->updater->updateDeals($id, $deals);
    }

    /**
     * Detach all deals from a product.
     *
     * @param  int $id The product identifier.
     *
     * @return Product The updated product instance.
     */
    public function removeDeal(int $id): Product
    {
        return $this->destructor->removeDealFromProduct($id);
    }

    /**
     * Restore soft-deleted deal relationships on a product.
     *
     * @param  int $id The product identifier.
     *
     * @return Product The updated product instance.
     */
    public function restoreDeal(int $id): Product
    {
        return $this->destructor->restoreDealOnProduct($id);
    }
}
