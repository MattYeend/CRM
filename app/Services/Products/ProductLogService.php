<?php

namespace App\Services\Products;

use App\Models\Log;
use App\Models\Product;
use App\Models\User;

/**
 * Handles audit logging for Product lifecycle events.
 *
 * Each public method writes a structured log entry via the Log model for
 * a specific product action, combining base product data with
 * action-specific timestamp and actor fields.
 */
class ProductLogService
{
    /**
     * Log a product creation event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Product $product The product that was created.
     *
     * @return array The structured data written to the log entry.
     */
    public function productCreated(
        User $user,
        int $userId,
        Product $product
    ): array {
        $data = $this->baseProductData($product) + [
            'created_at' => now(),
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PRODUCT_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a product update event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Product $product The product that was updated.
     *
     * @return array The structured data written to the log entry.
     */
    public function productUpdated(
        User $user,
        int $userId,
        Product $product
    ): array {
        $data = $this->baseProductData($product) + [
            'updated_at' => now(),
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PRODUCT_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a product deletion event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Product $product The product that was deleted.
     *
     * @return array The structured data written to the log entry.
     */
    public function productDeleted(
        User $user,
        int $userId,
        Product $product
    ): array {
        $data = $this->baseProductData($product) + [
            'deleted_at' => now(),
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PRODUCT_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a product restoration event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Product $product The product that was restored.
     *
     * @return array The structured data written to the log entry.
     */
    public function productRestored(
        User $user,
        int $userId,
        Product $product
    ): array {
        $data = $this->baseProductData($product) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PRODUCT_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Build the base data array shared across all product log entries.
     *
     * @param  Product $product The product being logged.
     *
     * @return array The base fields extracted from the product.
     */
    protected function baseProductData(Product $product): array
    {
        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'currency' => $product->currency,
            'quantity' => $product->quantity,
            'meta' => $product->meta,
        ];
    }
}
