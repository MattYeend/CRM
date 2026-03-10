<?php

namespace App\Services;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;

class NoteManagementService
{
    private NoteCreatorService $creator;
    private NoteUpdaterService $updater;
    private NoteDestructorService $destructor;

    public function __construct(
        NoteCreatorService $creator,
        NoteUpdaterService $updater,
        NoteDestructorService $destructor,
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new note.
     *
     * @param StoreNoteRequest $request
     *
     * @return Note
     */
    public function store(StoreNoteRequest $request): Note
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing note.
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
        return $this->updater->update($request, $note);
    }

    /**
     * Delete a note (soft delete).
     *
     * @param Note $note
     *
     * @return void
     */
    public function destroy(Note $note): void
    {
        $this->destructor->destroy($note);
    }

    /**
     * Restore a soft-deleted note
     *
     * @param int $id
     *
     * @return Note
     */
    public function restore(int $id): Note
    {
        return $this->destructor->restore($id);
    }
}
