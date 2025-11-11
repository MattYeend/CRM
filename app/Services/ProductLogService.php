<?php

namespace App\Services;

use App\Models\Log;
use App\Models\Product;
use App\Models\User;

class ProductLogService
{
    public function __construct()
    {
        // Empty constructor
    }

    /**
     * Log the creation of a Product.
     *
     * @param User $user The user that created the product.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Product $product The product being logged.
     *
     * @return Log The created log entry.
     */
    public function productCreated(
        User $user,
        int $userId,
        Product $product
    ): array {
        $data = $this->baseProductData($product) + [
            'created_at' => $product->created_at,
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
     * Log the update of a Product.
     *
     * @param User $user The user that updated the product.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Product $product The product being logged.
     *
     * @return Log The created log entry.
     */
    public function productUpdated(
        User $user,
        int $userId,
        Product $product
    ): array {
        $data = $this->baseProductData($product) + [
            'updated_at' => $product->updated_at,
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
     * Log the deletion of a Product.
     *
     * @param User $user The user that deleted the product.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Product $product The product being logged.
     *
     * @return Log The created log entry.
     */
    public function productDeleted(
        User $user,
        int $userId,
        Product $product
    ): array {
        $data = $this->baseProductData($product) + [
            'deleted_at' => $product->deleted_at,
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
     * Log the restoration of a Product.
     *
     * @param User $user The user that restored the product.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Product $product The product being logged.
     *
     * @return Log The created log entry.
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
     * Prepare the base data for logging a Product.
     *
     * @param Product $product The product being logged.
     *
     * @return array The base data array.
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
