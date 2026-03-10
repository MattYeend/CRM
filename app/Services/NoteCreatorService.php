<?php

namespace App\Services;

use App\Http\Requests\StoreNoteRequest;
use App\Models\Company;
use App\Models\Deal;
use App\Models\Note;

class NoteCreatorService
{
    /**
     * Allowed polymorphic models
     */
    protected array $notableModels = [
        'deal' => Deal::class,
        'company' => Company::class,
    ];
    /**
     * Create a new note from request data.
     *
     * @param StoreNoteRequest $request
     *
     * @return Note
     */
    public function create(StoreNoteRequest $request): Note
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;

        $note = Note::create($data);

        $this->attachNoteToModel($note, $data);

        return $note;
    }

    /**
     * Attach the given note to a polymorphic model.
     *
     * The method checks the provided data array for `notable_type`
     * and `notable_id`. If both are present and the notable type
     * exists in the allowed model map, the note will be attached
     * to the corresponding model via the `notes()` relationship.
     *
     * If the type is not allowed or the model cannot be found,
     * the method safely exits without performing any action.
     *
     * @param Note  $note The note instance to attach.
     * @param array $data The validated request data containing
     *                    'notable_type' and 'notable_id'.
     *
     * @return void
     */
    protected function attachNoteToModel(Note $note, array $data): void
    {
        if (! isset($data['notable_type'], $data['notable_id'])) {
            return;
        }

        if (! isset($this->notableModels[$data['notable_type']])) {
            return;
        }

        $modelClass = $this->notableModels[$data['notable_type']];

        $model = $modelClass::find($data['notable_id']);

        if (! $model) {
            return;
        }

        $model->notes()->save($note);
    }
}
