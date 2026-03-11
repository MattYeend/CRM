<?php

namespace App\Services\Notes;

use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;

class NoteUpdaterService
{
    /**
     * Update the note using request data.
     *
     * @param UpdateNoteRequest $request
     *
     * @param Note $note
     *
     * @return Note
     */
    public function update(
        UpdateNoteRequest $request,
        Note $note
    ): Note {
        $user = $request->user();
        $data = $request->validated();

        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        $note->update($data);

        return $note->fresh();
    }
}
