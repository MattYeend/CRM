<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for updating an existing PartCategory.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — aggregates parent association and category name rules
 *   - parentRules — optional parent category relationship with self-exclusion
 *   - nameRules — unique category name with current model ignored
 *   - metaRules — optional description, test flag, and meta payload
 */
class UpdatePartCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound part category and delegates to the
     * 'update' policy.
     *
     * @return bool True if the authenticated user may update this part
     * category.
     */
    public function authorize(): bool
    {
        $partCategory = $this->route('partCategory');

        return $this->user()->can('update', $partCategory);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Merges base and meta rule groups into a single ruleset.
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
     * Aggregate validation rules for all updatable part category fields.
     *
     * Combines parent and name rule groups into a single base ruleset.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return array_merge(
            $this->parentRules(),
            $this->nameRules(),
        );
    }

    /**
     * Validation rules for the optional parent category relationship.
     *
     * Ensures the parent_id references an existing part category and
     * cannot reference the current category itself.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
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
     * Validation rules for the category name field.
     *
     * Ensures the name is unique across part categories, excluding the
     * current model being updated.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
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
     * Validation rules for optional descriptive and metadata fields.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
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
