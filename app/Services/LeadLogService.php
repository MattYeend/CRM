<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Log;
use App\Models\User;

class LeadLogService
{
    public function __construct()
    {
        // Empty constructor
    }

    /**
     * Log the creation of a Lead.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Lead $lead The lead that was created.
     *
     * @return Log The created log entry.
     */
    public function leadCreated(
        User $user,
        int $userId,
        Lead $lead
    ): array {
        $data = $this->baseLeadData($lead) + [
            'created_at' => $lead->created_at,
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_LEAD_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the update of a lead.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Lead $lead The lead that was updated.
     *
     * @return Log The created log entry.
     */
    public function leadUpdated(
        User $user,
        int $userId,
        Lead $lead
    ): array {
        $data = $this->baseLeadData($lead) + [
            'updated_at' => $lead->updated_at,
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_LEAD_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the deletion of a lead.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Lead $lead The lead that was deleted.
     *
     * @return Log The created log entry.
     */
    public function leadDeleted(
        User $user,
        int $userId,
        Lead $lead
    ): array {
        $data = $this->baseLeadData($lead) + [
            'deleted_at' => $lead->deleted_at,
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_LEAD_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the restoration of a lead.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Lead $lead The lead that was restored.
     *
     * @return Log The created log entry.
     */
    public function leadRestored(
        User $user,
        int $userId,
        Lead $lead
    ): array {
        $data = $this->baseLeadData($lead) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_LEAD_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the assignment of a lead to a new owner.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Lead $lead The lead that was reassigned.
     *
     * @return Log The created log entry.
     */
    public function leadAssigned(
        User $user,
        int $userId,
        Lead $lead,
    ): array {
        $data = $this->baseLeadData($lead) + [
            'assigned_at' => now(),
            'assigned_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_LEAD_ASSIGNED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Build the common data array for a Lead log entry.
     *
     * @param Lead $lead
     *
     * @return array
     */
    private function baseLeadData($lead): array
    {
        return [
            'id' => $lead->id,
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'source' => $lead->source,
            'owner_id' => $lead->owner_id,
            'meta' => $lead->meta,
        ];
    }
}
