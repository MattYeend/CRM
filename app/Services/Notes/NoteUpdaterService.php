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
        $data = $request->validated();

        $data['updated_by'] = $request->user()->id;

        $note->update($data);

        return $note->fresh();
    }
}
