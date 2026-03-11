<?php

namespace App\Services\Notes;

use App\Models\Note;

class NoteDestructorService
{
    /**
     * Soft-delete a note.
     *
     * @param Note $note
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
     * Restore a trashed note.
     *
     * @param int $id
     *
     * @return Note
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
