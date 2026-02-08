<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\Log;
use App\Models\User;

class DealLogService
{
    public function __construct()
    {
        // Empty constructor
    }

    /**
     * Log the creation of a deal.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Deal $deal The deal that was created.
     *
     * @return Log The created log entry.
     */
    public function dealCreated(
        User $user,
        int $userId,
        Deal $deal
    ): array {
        $data = $this->baseDealData($deal) + [
            'created_at' => $deal->created_at,
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
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Deal $deal The deal that was updated.
     *
     * @return Log The created log entry.
     */
    public function dealUpdated(
        User $user,
        int $userId,
        Deal $deal
    ): array {
        $data = $this->baseDealData($deal) + [
            'updated_at' => $deal->updated_at,
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
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Deal $deal The deal that was deleted.
     *
     * @return Log The created log entry.
     */
    public function dealDeleted(
        User $user,
        int $userId,
        Deal $deal
    ): array {
        $data = $this->baseDealData($deal) + [
            'deleted_at' => $deal->deleted_at,
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
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Deal $deal The deal that was restored.
     *
     * @return Log The created log entry.
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
     * Build the common data array for a Deal log entry.
     *
     * @param Deal $deal
     *
     * @return array
     */
    private function baseDealData(Deal $deal): array
    {
        return [
            'id' => $deal->id,
            'title' => $deal->title,
            'company_id' => $deal->company_id,
            'contact_id' => $deal->contact_id,
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
