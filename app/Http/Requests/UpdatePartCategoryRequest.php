<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePartCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $partCategory = $this->route('partCategory');

        return $this->user()->can('update', $partCategory);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
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
        return array_merge(
            $this->parentRules(),
            $this->nameAndSlugRules(),
        );
    }

    /**
     * Parent rules
     *
     * @return array
     */
    private function parentRules(): array
    {
        $partCategory = $this->route('partCategory');

        return [
            'parent_id' => [
                'nullable',
                'exists:part_categories,id',
                Rule::notIn([$partCategory]),
            ],
        ];
    }
    /**
     * Name and slug rules
     *
     * @return array
     */
    private function nameAndSlugRules(): array
    {
        $partCategory = $this->route('partCategory');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('part_categories', 'name')
                    ->ignore($partCategory),
            ],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('part_categories', 'slug')
                    ->ignore($partCategory),
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
            'description' => 'nullable|string|max:255',
            'is_test' => 'nullable|boolean',
            'meta' => 'nullable|array',
        ];
    }
}
