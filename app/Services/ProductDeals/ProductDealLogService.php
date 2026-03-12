<?php

namespace App\Services\ProductDeals;

use App\Models\Log;
use App\Models\ProductDeal;
use App\Models\User;

class ProductDealLogService
{
    public function __construct()
    {
        // Empty constructor
    }

    /**
     * Log the creation of a Product Deal.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param ProductDeal $productDeal The product deal was created.
     *
     * @return Log The created log entry.
     */
    public function productDealCreated(
        User $user,
        int $userId,
        ProductDeal $productDeal
    ): array {
        $data = $this->baseProductData($productDeal) + [
            'created_at' => $productDeal->created_at,
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PRODUCT_DEAL_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the update of a Product Deal.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param ProductDeal $productDeal The product deal was updated.
     *
     * @return Log The created log entry.
     */
    public function productDealUpdated(
        User $user,
        int $userId,
        ProductDeal $productDeal
    ): array {
        $data = $this->baseProductData($productDeal) + [
            'updated_at' => $productDeal->updated_at,
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PRODUCT_DEAL_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the deletion of a Product Deal.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param ProductDeal $productDeal The product deal was deleted.
     *
     * @return Log The created log entry.
     */
    public function productDealDeleted(
        User $user,
        int $userId,
        ProductDeal $productDeal
    ): array {
        $data = $this->baseProductData($productDeal) + [
            'deleted_at' => $productDeal->deleted_at,
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PRODUCT_DEAL_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the restoration of a Product Deal.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param ProductDeal $productDeal The product deal was restored.
     *
     * @return Log The created log entry.
     */
    public function productDealRestored(
        User $user,
        int $userId,
        ProductDeal $productDeal
    ): array {
        $data = $this->baseProductData($productDeal) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PRODUCT_DEAL_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Prepare the base data for logging a Product Deal.
     *
     * @param ProductDeal $productDeal The product deal being logged.
     *
     * @return array The base data array.
     */
    protected function baseProductData(ProductDeal $productDeal): array
    {
        return [
            'id' => $productDeal->id,
            'product_id' => $productDeal->product_id,
            'deal_id' => $productDeal->deal_id,
            'quantity' => $productDeal->quantity,
            'unit_price' => $productDeal->unit_price,
            'total_price' => $productDeal->total_price,
            'currency' => $productDeal->currency,
            'meta' => $productDeal->meta,
        ];
    }
}
