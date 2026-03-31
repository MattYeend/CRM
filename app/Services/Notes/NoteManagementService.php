<?php

namespace App\Services\Notes;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;

/**
 * Orchestrates note lifecycle operations by delegating to focused
 * sub-services.
 *
 * Acts as the single entry point for note create, update, delete, and
 * restore operations, keeping controllers decoupled from the underlying
 * service implementations.
 */
class NoteManagementService
{
    /**
     * Service responsible for creating new note records.
     *
     * @var NoteCreatorService
     */
    private NoteCreatorService $creator;

    /**
     * Service responsible for updating existing note records.
     *
     * @var NoteUpdaterService
     */
    private NoteUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring note records.
     *
     * @var NoteDestructorService
     */
    private NoteDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  NoteCreatorService $creator Handles note creation.
     * @param  NoteUpdaterService $updater Handles note updates.
     * @param  NoteDestructorService $destructor Handles note deletion
     * and restoration.
     */
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
     * @param  StoreNoteRequest $request Validated request containing note
     * data.
     *
     * @return Note The newly created note.
     */
    public function store(StoreNoteRequest $request): Note
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing note.
     *
     * @param  UpdateNoteRequest $request Validated request containing
     * updated note data.
     * @param  Note $note The note instance to update.
     *
     * @return Note The updated note.
     */
    public function update(
        UpdateNoteRequest $request,
        Note $note
    ): Note {
        return $this->updater->update($request, $note);
    }

    /**
     * Soft-delete a note.
     *
     * Delegates to the destructor service to perform a soft-delete.
     *
     * @param  Note $note The note to delete.
     *
     * @return void
     */
    public function destroy(Note $note): void
    {
        $this->destructor->destroy($note);
    }

    /**
     * Restore a soft-deleted note.
     *
     * @param  int $id The primary key of the soft-deleted note.
     *
     * @return Note The restored note.
     */
    public function restore(int $id): Note
    {
        return $this->destructor->restore($id);
    }
}
