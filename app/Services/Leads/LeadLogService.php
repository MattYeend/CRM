<?php

namespace App\Services\Leads;

use App\Models\Lead;
use App\Models\Log;
use App\Models\User;

/**
 * Handles audit logging for Lead lifecycle events.
 *
 * Each public method writes a structured log entry via the Log model for
 * a specific lead action, combining base lead data with
 * action-specific timestamp and actor fields.
 */
class LeadLogService
{
    /**
     * Log a lead creation event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Lead $lead The lead that was created.
     *
     * @return array The structured data written to the log entry.
     */
    public function leadCreated(
        User $user,
        int $userId,
        Lead $lead
    ): array {
        $data = $this->baseLeadData($lead) + [
            'created_at' => now(),
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
     * Log a lead update event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Lead $lead The lead that was updated.
     *
     * @return array The structured data written to the log entry.
     */
    public function leadUpdated(
        User $user,
        int $userId,
        Lead $lead
    ): array {
        $data = $this->baseLeadData($lead) + [
            'updated_at' => now(),
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
     * Log a lead deletion event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Lead $lead The lead that was deleted.
     *
     * @return array The structured data written to the log entry.
     */
    public function leadDeleted(
        User $user,
        int $userId,
        Lead $lead
    ): array {
        $data = $this->baseLeadData($lead) + [
            'deleted_at' => now(),
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
     * Log a lead restoration event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Lead $lead The lead that was restored.
     *
     * @return array The structured data written to the log entry.
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
     * Log a lead assignment event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Lead $lead The lead that was assigned.
     *
     * @return array The structured data written to the log entry.
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
     * Build the base data array shared across all lead log entries.
     *
     * @param  Lead $lead The lead being logged.
     *
     * @return array The base fields extracted from the lead.
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
