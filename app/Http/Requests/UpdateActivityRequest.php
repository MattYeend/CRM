<?php

namespace App\Http\Requests;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $activity = $this->route('activity');

        return $this->user()->can('update', $activity);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
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
     * Convert subject_type from morph key to full class before validation
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
     * Base rules
     *
     * @return array
     */
    private function baseRules(): array
    {
        return [
            'type' => 'sometimes|string',
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ],
        ];
    }

    /**
     * Subject rules
     *
     * @return array
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
     * Meta rules
     *
     * @return array
     */
    private function metaRules(): array
    {
        return [
            'description' => 'nullable|string',
            'meta' => 'nullable|array',
        ];
    }
}
