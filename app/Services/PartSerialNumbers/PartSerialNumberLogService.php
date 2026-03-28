<?php

namespace App\Services\PartSerialNumbers;

use App\Models\Log;
use App\Models\PartSerialNumber;
use App\Models\User;

class PartSerialNumberLogService
{
    /**
     * Log the creation of a Part Serial Number.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param PartSerialNumber $partSerialNumber The part was created.
     *
     * @return Log The created log entry.
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
     * Log the update of a Part Serial Number.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param PartSerialNumber $partSerialNumber The part was updated.
     *
     * @return Log The created log entry.
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
     * Log the deletion of a Part Serial Number.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param PartSerialNumber $partSerialNumber The part was deleted.
     *
     * @return Log The created log entry.
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
     * Log the restoration of a Part Serial Number.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param PartSerialNumber $partSerialNumberThe part was restored.
     *
     * @return Log The created log entry.
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
     * Prepare the base data for logging a Part Serial Number.
     *
     * @param PartSerialNumber $partSerialNumber The part being logged.
     *
     * @return array The base data array.
     */
    protected function basePartSerialNumberData(PartSerialNumber $partSerialNumber): array
    {
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
