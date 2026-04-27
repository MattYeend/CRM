<?php

namespace App\Http\Requests;

use App\Models\BillOfMaterial;
use App\Models\Part;
use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for storing a new BillOfMaterial.
 *
 * Validates the parent-child part relationship, ensuring the child part
 * exists and differs from the parent, along with quantity, scrap percentage,
 * unit of measure, and optional notes.
 */
class StoreBillOfMaterialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the BillOfMaterial model.
     *
     * @return bool True if the authenticated user may create bills of
     * material.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', BillOfMaterial::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Enforces that the child part exists and is distinct from the parent,
     * that quantity is a positive number, and that all optional fields are
     * of the correct type when provided.
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
     * Resolve the manufacturable_type morph key to its full class name before
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
        $type = $this->route('type');
        $id = $this->route('manufacturable');

        $manufacturableType = match ($type) {
            'parts' => Part::class,
            'products' => Product::class,
            default => null,
        };

        $this->merge([
            'manufacturable_type' => $manufacturableType,
            'manufacturable_id' => $id,
        ]);
    }

    /**
     * Validation rules for core activity fields.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'child_part_id' => [
                'required',
                Rule::exists('parts', 'id'),
            ],
            'quantity' => 'required|numeric|min:0.0001',
            'scrap_percentage' => 'nullable|numeric|min:0|max:100',
            'unit_of_measure' => 'nullable|string',
        ];
    }

    /**
     * Validation rules for the polymorphic manufactured relationship.
     *
     * Ensures manufacturable_type is one of the registered activity types and
     * that manufacturable_id is present whenever a manufacturable_type is
     * provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function subjectRules(): array
    {
        return [
            'manufacturable_type' => [
                'required',
                Rule::in(BillOfMaterial::BOM_TYPES),
            ],
            'manufacturable_id' => [
                'required',
                'integer',
                'required_with:manufacturable_type',
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
            'notes' => 'nullable|string',
        ];
    }
}
