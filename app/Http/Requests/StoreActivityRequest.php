<?php

namespace App\Http\Requests;

use App\Models\Activity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Relations\Relation;

class StoreActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Activity::class);
    }

    /**
     * Convert subject_type from morph key to full class before validation
     */
    protected function prepareForValidation(): void
    {
        if ($this->subject_type) {
            $this->merge([
                'subject_type' => Relation::getMorphedModel($this->subject_type),
            ]);
        }
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
            $this->metaRules(),
        );
    }

    /**
     * Base rules
     *
     * @return array
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
     * Subject rules
     *
     * @return array
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
