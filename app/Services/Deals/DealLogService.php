<?php

namespace App\Services\Deals;

use App\Models\Deal;
use App\Models\Log;
use App\Models\User;

/**
 * Handles logging of Deal-related actions.
 *
 * Provides methods to log creation, update, deletion, and restoration
 * of deal records, recording the responsible user and timestamps.
 */
class DealLogService
{
    /**
     * Log the creation of a deal.
     *
     * Records the user who created the deal and the timestamp.
     *
     * @param  User $user The user performing the action.
     * @param  int $userId The ID of the user performing the action.
     * @param  Deal $deal The deal being created.
     *
     * @return array The logged data for the creation action.
     */
    public function dealCreated(
        User $user,
        int $userId,
        Deal $deal
    ): array {
        $data = $this->baseDealData($deal) + [
            'created_at' => now(),
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_DEAL_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the update of a deal.
     *
     * Records the user who updated the deal and the timestamp.
     *
     * @param  User $user The user performing the action.
     * @param  int $userId The ID of the user performing the action.
     * @param  Deal $deal The deal being updated.
     *
     * @return array The logged data for the update action.
     */
    public function dealUpdated(
        User $user,
        int $userId,
        Deal $deal
    ): array {
        $data = $this->baseDealData($deal) + [
            'updated_at' => now(),
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_DEAL_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the deletion of a deal.
     *
     * Records the user who deleted the deal and the timestamp.
     *
     * @param  User $user The user performing the action.
     * @param  int $userId The ID of the user performing the action.
     * @param  Deal $deal The deal being deleted.
     *
     * @return array The logged data for the deletion action.
     */
    public function dealDeleted(
        User $user,
        int $userId,
        Deal $deal
    ): array {
        $data = $this->baseDealData($deal) + [
            'deleted_at' => now(),
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_DEAL_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the restoration of a deal.
     *
     * Records the user who restored the deal and the timestamp.
     *
     * @param  User $user The user performing the action.
     * @param  int $userId The ID of the user performing the action.
     * @param  Deal $deal The deal being restored.
     *
     * @return array The logged data for the restoration action.
     */
    public function dealRestored(
        User $user,
        int $userId,
        Deal $deal
    ): array {
        $data = $this->baseDealData($deal) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_DEAL_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Prepare the base data for logging a deal.
     *
     * Extracts relevant attributes from the deal for logging purposes.
     *
     * @param  Deal $deal The deal to extract data from.
     *
     * @return array The base data array to be included in logs.
     */
    private function baseDealData(Deal $deal): array
    {
        return [
            'id' => $deal->id,
            'title' => $deal->title,
            'company_id' => $deal->company_id,
            'owner_id' => $deal->owner_id,
            'pipeline_id' => $deal->pipeline_id,
            'stage_id' => $deal->stage_id,
            'value' => $deal->value,
            'currency' => $deal->currency,
            'close_date' => $deal->close_date,
            'status' => $deal->status,
        ];
    }
}
