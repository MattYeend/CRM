<?php

namespace App\Services\Notes;

use App\Models\Note;

/**
 * Handles soft deletion and restoration of Note records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by, and
 * restored_at columns are always populated.
 */
class NoteDestructorService
{
    /**
     * Soft-delete a note.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the note.
     *
     * @param  Note $note The note instance to soft-delete.
     *
     * @return void
     */
    public function destroy(Note $note): void
    {
        $userId = auth()->id();

        $note->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);

        $note->delete();
    }

    /**
     * Restore a soft-deleted note.
     *
     * Looks up the note including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the note. Returns the note unchanged if it is not currently
     * trashed.
     *
     * @param  int $id The primary key of the soft-deleted note.
     *
     * @return Note The restored note instance.
     */
    public function restore(int $id): Note
    {
        $userId = auth()->id();

        $note = Note::withTrashed()->findOrFail($id);

        if ($note->trashed()) {
            $note->update([
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);
            $note->restore();
        }

        return $note;
    }
}
