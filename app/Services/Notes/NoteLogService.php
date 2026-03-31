<?php

namespace App\Services\Notes;

use App\Models\Log;
use App\Models\Note;
use App\Models\User;

/**
 * Handles audit logging for Note lifecycle events.
 *
 * Each public method writes a structured log entry via the Log model for
 * a specific note action, combining base note data with
 * action-specific timestamp and actor fields.
 */
class NoteLogService
{
    /**
     * Log a note creation event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Note $note The note that was created.
     *
     * @return array The structured data written to the log entry.
     */
    public function noteCreated(
        User $user,
        int $userId,
        Note $note
    ): array {
        $data = $this->baseNoteData($note) + [
            'created_at' => now(),
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
     * Log a note update event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Note $note The note that was updated.
     *
     * @return array The structured data written to the log entry.
     */
    public function noteUpdated(
        User $user,
        int $userId,
        Note $note
    ): array {
        $data = $this->baseNoteData($note) + [
            'updated_at' => now(),
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
     * Log a note deletion event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Note $note The note that was deleted.
     *
     * @return array The structured data written to the log entry.
     */
    public function noteDeleted(
        User $user,
        int $userId,
        Note $note
    ): array {
        $data = $this->baseNoteData($note) + [
            'deleted_at' => now(),
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
     * Log a note restoration event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Note $note The note that was restored.
     *
     * @return array The structured data written to the log entry.
     */
    public function noteRestored(
        User $user,
        int $userId,
        Note $note
    ): array {
        $data = $this->baseNoteData($note) + [
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
     * Log a note assignment event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Note $note The note that was assigned.
     *
     * @return array The structured data written to the log entry.
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
