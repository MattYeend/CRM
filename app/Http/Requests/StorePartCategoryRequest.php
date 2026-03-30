<?php

namespace App\Http\Requests;

use App\Models\PartCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for storing a new PartCategory.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — optional parent association and unique category name
 *   - metaRules — optional description, test flag, and meta payload
 */
class StorePartCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the PartCategory model.
     *
     * @return bool True if the authenticated user may create part categories.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', PartCategory::class);
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
     * Validation rules for the parent association and category name.
     *
     * The parent_id, when provided, must reference an existing part category.
     * The name must be unique across all part categories.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'parent_id' => 'nullable|exists:part_categories,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('part_categories', 'name'),
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
