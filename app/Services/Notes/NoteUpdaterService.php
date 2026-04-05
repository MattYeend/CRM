<?php

namespace App\Services\Notes;

use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;

/**
 * Handles updates to Note records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * updates to the note.
 */
class NoteUpdaterService
{
    /**
     * Update an existing note.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user and timestamp to audit fields, updates the note, and returns
     * a fresh instance.
     *
     * @param  UpdateNoteRequest $request The request containing
     * validated note data.
     * @param  Note $note The note to update.
     *
     * @return Note The updated note instance.
     */
    public function update(
        UpdateNoteRequest $request,
        Note $note
    ): Note {
        $user = $request->user();
        $data = $request->validated();

        $data['updated_by'] = $user->id;

        $note->update($data);

        return $note->fresh();
    }
}
