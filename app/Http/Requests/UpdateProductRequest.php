<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for updating an existing Product.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — core product identity and pricing fields
 *   - statusRules — product status constrained to allowed values
 *   - quantityRules — stock level and reorder threshold fields
 *   - metaRules — optional metadata payload
 */
class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound product and delegates to the 'update' policy.
     *
     * @return bool True if the authenticated user may update this product.
     */
    public function authorize(): bool
    {
        $product = $this->route('product');

        return $this->user()->can('update', $product);
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
     * Ensures the SKU remains unique when provided, excluding the current
     * model being updated. All fields are optional but must conform to
     * expected types when present.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        $product = $this->route('product');

        return [
            'sku' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->ignore($product),
            ],
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'currency' => 'nullable|string|max:8',
        ];
    }

    /**
     * Validation rules for the product status field.
     *
     * Restricts the value to the set of statuses defined on the Product model.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function statusRules(): array
    {
        return [
            'status' => [
                'sometimes',
                Rule::in(Product::PRODUCT_STATUSES),
            ],
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
