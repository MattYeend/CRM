<?php

namespace App\Services\PartSerialNumbers;

use App\Models\Log;
use App\Models\PartSerialNumber;
use App\Models\User;

/**
 * Handles audit logging for PartSerialNumber lifecycle events.
 *
 * Each public method writes a structured log entry via the Log model for
 * a specific part serial number action, combining base part serial number
 * data with action-specific timestamp and actor fields.
 */
class PartSerialNumberLogService
{
    /**
     * Log a part serial number creation event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  PartSerialNumber $partSerialNumber The part serial number that
     * was created.
     *
     * @return array The structured data written to the log entry.
     */
    public function partSerialNumberCreated(
        User $user,
        int $userId,
        PartSerialNumber $partSerialNumber
    ): array {
        $data = $this->basePartSerialNumberData($partSerialNumber) + [
            'created_at' => now(),
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PART_SERIAL_NUMBER_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a part serial number update event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  PartSerialNumber $partSerialNumber The part serial number that
     * was updated.
     *
     * @return array The structured data written to the log entry.
     */
    public function partSerialNumberUpdated(
        User $user,
        int $userId,
        PartSerialNumber $partSerialNumber
    ): array {
        $data = $this->basePartSerialNumberData($partSerialNumber) + [
            'updated_at' => now(),
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PART_SERIAL_NUMBER_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a part serial number deletion event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  PartSerialNumber $partSerialNumber The part serial number that
     * was deleted.
     *
     * @return array The structured data written to the log entry.
     */
    public function partSerialNumberDeleted(
        User $user,
        int $userId,
        PartSerialNumber $partSerialNumber
    ): array {
        $data = $this->basePartSerialNumberData($partSerialNumber) + [
            'deleted_at' => now(),
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PART_SERIAL_NUMBER_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a part serial number restoration event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  PartSerialNumber $partSerialNumber The part serial number that
     * was restored.
     *
     * @return array The structured data written to the log entry.
     */
    public function partSerialNumberRestored(
        User $user,
        int $userId,
        PartSerialNumber $partSerialNumber
    ): array {
        $data = $this->basePartSerialNumberData($partSerialNumber) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PART_SERIAL_NUMBER_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Build the base data array shared across all part serial number log
     * entries.
     *
     * @param  PartSerialNumber $partSerialNumber The part serial number being
     * logged.
     *
     * @return array The base fields extracted from the part serial number.
     */
    protected function basePartSerialNumberData(
        PartSerialNumber $partSerialNumber
    ): array {
        return [
            'id' => $partSerialNumber->id,
            'part_id' => $partSerialNumber->part_id,
            'serial_number' => $partSerialNumber->serial_number,
            'status' => $partSerialNumber->status,
            'batch_number' => $partSerialNumber->batch_number,
            'manufactured_at' => $partSerialNumber->manufactured_at,
            'expires_at' => $partSerialNumber->expires_at,
            'meta' => $partSerialNumber->meta,
        ];
    }
}
