<?php

namespace App\Http\Requests;

use App\Models\Activity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for storing a new Activity.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — core activity fields
 *   - subjectRules — polymorphic subject type and ID
 *   - metaRules — optional description and meta payload
 *
 * prepareForValidation resolves the incoming morph key to its full class
 * name before the ruleset is applied.
 */
class StoreActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the Activity model.
     *
     * @return bool True if the authenticated user may create activities.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Activity::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Merges base, subject, and meta rule groups into a single ruleset.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            $this->subjectRules(),
            $this->metaRules(),
        );
    }

    /**
     * Resolve the subject_type morph key to its full class name before
     * validation runs.
     *
     * Ensures that polymorphic type values sent as morph aliases are
     * expanded to their fully-qualified class names so they pass the
     * Rule::in check in subjectRules().
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->subject_type) {
            $this->merge([
                'subject_type' => Relation::getMorphedModel(
                    $this->subject_type
                ),
            ]);
        }
    }

    /**
     * Validation rules for core activity fields.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'type' => 'required|string',
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ],
        ];
    }

    /**
     * Validation rules for the polymorphic subject relationship.
     *
     * Ensures subject_type is one of the registered activity types and
     * that subject_id is present whenever a subject_type is provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function subjectRules(): array
    {
        return [
            'subject_type' => [
                'required',
                Rule::in(Activity::ACTIVITY_TYPES),
            ],
            'subject_id' => [
                'required',
                'integer',
                'required_with:subject_type',
            ],
        ];
    }

    /**
     * Validation rules for optional descriptive and metadata fields.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function metaRules(): array
    {
        return [
            'description' => 'nullable|string',
            'meta' => 'nullable|array',
        ];
    }
}
