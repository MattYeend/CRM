<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Log;
use App\Models\User;

class ContactLogService
{
    public function __construct()
    {
        // Empty constructor
    }

    /**
     * Log the creation of a contact.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Contact $contact The contact that was created.
     *
     * @return Log The created log entry.
     */
    public function contactCreated(
        User $user,
        int $userId,
        Contact $contact
    ): array {
        $data = $this->baseContactData($contact) + [
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
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Contact $contact The contact that was updated.
     *
     * @return Log The created log entry.
     */
    public function contactUpdated(
        User $user,
        int $userId,
        Contact $contact
    ): array {
        $data = $this->baseContactData($contact) + [
            'updated_at' => $contact->updated_at,
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
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Contact $contact The contact that was deleted.
     *
     * @return Log The created log entry.
     */
    public function contactDeleted(
        User $user,
        int $userId,
        Contact $contact
    ): array {
        $data = $this->baseContactData($contact) + [
            'deleted_at' => $contact->deleted_at,
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
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Contact $contact The contact that was restored.
     *
     * @return Log The created log entry.
     */
    public function contactRestored(
        User $user,
        int $userId,
        Contact $contact
    ): array {
        $data = $this->baseContactData($contact) + [
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

    /**
     * Build the common data array for a Contact log entry.
     *
     * @param Contact $contact
     *
     * @return array
     */
    private function baseContactData(Contact $contact): array
    {
        return [
            'id' => $contact->id,
            'company_id' => $contact->company_id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'job_title' => $contact->job_title,
        ];
    }
}
