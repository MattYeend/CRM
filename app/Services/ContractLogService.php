<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Log;
use App\Models\User;

class ContractLogService
{
    public function __construct()
    {
        // Empty constructor
    }

    /**
     * Log the creation of a contact.
     *
     * @param User $user The user that was created.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Contact $contact The contact being logged.
     *
     * @return Log The created log entry.
     */
    public function contractCreated(
        User $user,
        int $userId,
        Contact $contact
    ): array {
        $data = [
            'id' => $contact->id,
            'company_id' => $contact->company_id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'job_title' => $contact->job_title,
            'created_at' => $contact->created_at,
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_CONTACT_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the update of a contact.
     *
     * @param User $user The user that was updated.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Contact $contact The contact being logged.
     *
     * @return Log The created log entry.
     */
    public function contractUpdated(
        User $user,
        int $userId,
        Contact $contact
    ): array {
        $data = [
            'id' => $contact->id,
            'company_id' => $contact->company_id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'job_title' => $contact->job_title,
            'updated_at' => now(),
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_CONTACT_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the deletion of a contact.
     *
     * @param User $user The user that was deleted.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Contact $contact The contact being logged.
     *
     * @return Log The created log entry.
     */
    public function contractDeleted(
        User $user,
        int $userId,
        Contact $contact
    ): array {
        $data = [
            'id' => $contact->id,
            'company_id' => $contact->company_id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'job_title' => $contact->job_title,
            'deleted_at' => now(),
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_CONTACT_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the restoring of a contact.
     *
     * @param User $user The user that was restored.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Contact $contact The contact being logged.
     *
     * @return Log The created log entry.
     */
    public function contractRestored(
        User $user,
        int $userId,
        Contact $contact
    ): array {
        $data = [
            'id' => $contact->id,
            'company_id' => $contact->company_id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'job_title' => $contact->job_title,
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_CONTACT_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }
}
