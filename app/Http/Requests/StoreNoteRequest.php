<?php

namespace App\Http\Requests;

use App\Models\Note;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for storing a new Note.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — optional user association and the required note body
 *   - noteRules — polymorphic notable type and ID
 *   - metaRules — optional meta payload
 */
class StoreNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the Note model.
     *
     * @return bool True if the authenticated user may create notes.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Note::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Merges base, note, and meta rule groups into a single ruleset.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            $this->noteRules(),
            $this->metaRules(),
        );
    }

    /**
     * Resolve the notable_type morph key to its full class name before
     * validation runs.
     *
     * Ensures that polymorphic type values sent as morph aliases are
     * expanded to their fully-qualified class names so they pass the
     * Rule::in check in noteRules().
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->notable_type) {
            $this->merge([
                'notable_type' => Relation::getMorphedModel(
                    $this->notable_type
                ),
            ]);
        }
    }

    /**
     * Validation rules for the user association and note body.
     *
     * Ensures user_id, when provided, references an existing user record,
     * and that the note body is present.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'body' => 'required|string',
        ];
    }

    /**
     * Validation rules for the polymorphic notable relationship.
     *
     * Ensures notable_type is one of the registered notable types and
     * that notable_id is present whenever a notable_type is provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function noteRules(): array
    {
        return [
            'notable_type' => [
                'required',
                Rule::in(Note::NOTABLE_TYPES),
            ],
            'notable_id' => [
                'required',
                'integer',
                'required_with:notable_type',
            ],
        ];
    }

    /**
     * Validation rules for optional metadata fields.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function metaRules(): array
    {
        return [
            'meta' => 'nullable|array',
        ];
    }
}
