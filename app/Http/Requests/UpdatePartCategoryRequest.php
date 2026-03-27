<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
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
     * @return array<string,ValidationRule|array<mixed>|string>
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
            $this->nameRules(),
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
                'integer',
                'exists:part_categories,id',
                Rule::notIn([$partCategory->id]),
            ],
        ];
    }
    /**
     * Name rules
     *
     * @return array
     */
    private function nameRules(): array
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
