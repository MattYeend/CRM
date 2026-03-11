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
        $note->update([
            'deleted_by' => auth()->id(),
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
        $note = Note::withTrashed()->findOrFail($id);

        if ($note->trashed()) {
            $note->restore();
        }

        return $note;
    }
}
