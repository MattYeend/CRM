<?php

namespace App\Services;

use App\Models\Log;
use App\Models\Note;
use App\Models\User;

class NoteLogService
{
    public function __construct()
    {
        // Empty constructor
    }

    /**
     * Log the creation of a Note.
     *
     * @param User $user The user that created the note.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Note $note The note being logged.
     *
     * @return Log The created log entry.
     */
    public function noteCreated(
        User $user,
        int $userId,
        Note $note
    ): array {
        $data = $this->baseNoteData($note, $user) + [
            'created_at' => $note->created_at,
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_NOTE_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the update of a Note.
     *
     * @param User $user The user that updated the note.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Note $note The note being logged.
     *
     * @return Log The created log entry.
     */
    public function noteUpdated(
        User $user,
        int $userId,
        Note $note
    ): array {
        $data = $this->baseNoteData($note, $user) + [
            'updated_at' => $note->updated_at,
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_NOTE_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the deletion of a Note.
     *
     * @param User $user The user that deleted the note.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Note $note The note being logged.
     *
     * @return Log The created log entry.
     */
    public function noteDeleted(
        User $user,
        int $userId,
        Note $note
    ): array {
        $data = $this->baseNoteData($note, $user) + [
            'deleted_at' => $note->deleted_at,
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_NOTE_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the restoration of a Note.
     *
     * @param User $user The user that restored the note.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Note $note The note being logged.
     *
     * @return Log The created log entry.
     */
    public function noteRestored(
        User $user,
        int $userId,
        Note $note
    ): array {
        $data = $this->baseNoteData($note, $user) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_NOTE_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Prepare base data for Note logging.
     *
     * @param Note $note The note being logged.
     *
     * @param User $user The user performing the action.
     *
     * @return array The base data for logging.
     */
    protected function baseNoteData(Note $note): array
    {
        return [
            'id' => $note->id,
            'notable_type' => $note->notable_type,
            'notable_id' => $note->notable_id,
            'user_id' => $note->user_id,
            'body' => $note->body,
            'meta' => $note->meta,
        ];
    }
}
