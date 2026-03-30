<?php

namespace App\Http\Requests;

use App\Models\Note;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for updating an existing Note.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — optional user association and the required note body
 *   - noteRules — polymorphic notable type and ID
 *   - metaRules — optional meta payload
 */
class UpdateNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound note and delegates to the 'update' policy.
     *
     * @return bool True if the authenticated user may update this note.
     */
    public function authorize(): bool
    {
        $note = $this->route('note');

        return $this->user()->can('update', $note);
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
     * Validation rules for optional metadata fields.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    public function baseRules(): array
    {
        return [
            'body' => 'sometimes|required|string',
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
    public function noteRules(): array
    {
        return [
            'notable_type' => [
                'nullable',
                Rule::in(Note::NOTABLE_TYPES),
            ],
            'notable_id' => [
                'nullable',
                'integer',
                'required_with:notable_type',
            ],
        ];
    }

    /**
     * Meta rules
     *
     * @return array
     */
    public function metaRules(): array
    {
        return [
            'meta' => 'nullable|array',
        ];
    }
}
