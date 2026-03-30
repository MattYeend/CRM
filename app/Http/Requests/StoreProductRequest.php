<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for storing a new Product.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — core product identity and pricing fields
 *   - statusRules — product status constrained to allowed values
 *   - quantityRules — stock level and reorder threshold fields
 *   - metaRules — optional meta payload
 */
class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the Product model.
     *
     * @return bool True if the authenticated user may create products.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Product::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Merges base, status, quantity, and meta rule groups into a single
     * ruleset.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            $this->statusRules(),
            $this->quantityRules(),
            $this->metaRules(),
        );
    }

    /**
     * Validation rules for core product identity and pricing fields.
     *
     * The SKU must be unique across all products when provided. Name is
     * required; all other fields are optional.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'sku' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'sku'),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'currency' => 'nullable|string|max:8',
        ];
    }

    /**
     * Validation rules for the product status field.
     *
     * Constrains the value to the set of statuses defined on the Product
     * model.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function statusRules(): array
    {
        return [
            'status' => 'required', Rule::in(Product::PRODUCT_STATUSES),
        ];
    }

    /**
     * Validation rules for stock level and reorder threshold fields.
     *
     * All quantity fields are optional and must be non-negative integers
     * when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function quantityRules(): array
    {
        return [
            'quantity' => 'nullable|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'max_stock_level' => 'nullable|integer|min:0',
            'reorder_point' => 'nullable|integer|min:0',
            'reorder_quantity' => 'nullable|integer|min:0',
            'lead_time_days' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Validation rules for optional metadata fields.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function metaRules(): array
    {
        return [
            'meta' => 'nullable|array',
        ];
    }
}
