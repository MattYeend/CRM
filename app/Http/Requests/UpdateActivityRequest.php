<?php

namespace App\Http\Requests;

use App\Models\Activity;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for updating an existing Activity.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — optional type and user association fields
 *   - subjectRules — polymorphic subject type and ID
 *   - metaRules — optional description and meta payload
 *
 * prepareForValidation resolves the incoming morph key to its full class
 * name before the ruleset is applied.
 */
class UpdateActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound activity and delegates to the 'update' policy.
     *
     * @return bool True if the authenticated user may update this activity.
     */
    public function authorize(): bool
    {
        $activity = $this->route('activity');

        return $this->user()->can('update', $activity);
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
            $this->metaRules()
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
     * All fields are optional on update; assigned_to must reference an existing
     * user when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'type' => 'sometimes|string',
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ],
        ];
    }

    /**
     * Validation rules for the polymorphic subject relationship.
     *
     * Ensures subject_type, when provided, is one of the registered activity
     * types and that subject_id is present whenever a subject_type is given.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function subjectRules(): array
    {
        return [
            'subject_type' => [
                'nullable',
                Rule::in(Activity::ACTIVITY_TYPES),
            ],
            'subject_id' => [
                'nullable',
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
