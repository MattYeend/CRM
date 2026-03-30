<?php

namespace App\Http\Requests;

use App\Models\Part;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for updating an existing Part.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — core part identity fields including SKU and identifiers
 *   - typeRules — part type constrained to allowed values
 *   - statusRules — part status constrained to allowed values
 *   - relationshipRules — product, category, and supplier associations
 *   - physicalRules — dimensional and material attributes
 *   - pricingRules — price, cost, currency, tax, and discount fields
 *   - inventoryRules — stock levels, reorder thresholds, and locations
 *   - flagRules — boolean feature and state flags
 *   - metaRules — optional metadata payload
 */
class UpdatePartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound part and delegates to the 'update' policy.
     *
     * @return bool True if the authenticated user may update this part.
     */
    public function authorize(): bool
    {
        $part = $this->route('part');

        return $this->user()->can('update', $part);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Merges all rule groups into a single ruleset.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            $this->typeRules(),
            $this->statusRules(),
            $this->relationshipRules(),
            $this->physicalRules(),
            $this->pricingRules(),
            $this->inventoryRules(),
            $this->flagRules(),
            $this->metaRules(),
        );
    }

    /**
     * Validation rules for core part identity fields.
     *
     * Ensures identifiers such as SKU, part number, and barcode remain
     * unique when provided, excluding the current model being updated.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        $part = $this->route('part');

        return [
            'sku' => [
                'sometimes', 'string', 'max:255',
                Rule::unique('parts', 'sku')->ignore($part),
            ],
            'part_number' => [
                'nullable', 'string', 'max:255',
                Rule::unique('parts', 'part_number')->ignore($part),
            ],
            'barcode' => [
                'nullable', 'string', 'max:255',
                Rule::unique('parts', 'barcode')->ignore($part),
            ],
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:255',
            'brand' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'unit_of_measure' => 'sometimes|string|max:50',
        ];
    }

    /**
     * Validation rules for the part type field.
     *
     * Restricts the value to the supported set of part types.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function typeRules(): array
    {
        return [
            'type' => [
                'sometimes',
                Rule::in([
                    'raw_material',
                    'finished_good',
                    'consumable',
                    'spare_part',
                    'sub_assembly',
                ]),
            ],
        ];
    }

    /**
     * Validation rules for the part status field.
     *
     * Restricts the value to the statuses defined on the Part model.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function statusRules(): array
    {
        return [
            'status' => [
                'sometimes',
                Rule::in(Part::PART_STATUSES),
            ],
        ];
    }

    /**
     * Validation rules for product, category, and supplier associations.
     *
     * All relationships are optional but must reference existing records
     * when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function relationshipRules(): array
    {
        return [
            'product_id' => 'sometimes|exists:products,id',
            'category_id' => 'nullable|exists:part_categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ];
    }

    /**
     * Validation rules for physical dimension and material fields.
     *
     * All fields are optional but must be non-negative numerics or valid
     * strings when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function physicalRules(): array
    {
        return [
            'height' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'volume' => 'nullable|numeric|min:0',
            'colour' => 'nullable|string|max:255',
            'material' => 'nullable|string|max:255',
        ];
    }

    /**
     * Validation rules for pricing and tax fields.
     *
     * All fields are optional but must conform to valid numeric ranges
     * and formats when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function pricingRules(): array
    {
        return [
            'price' => 'sometimes|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'tax_rate' => 'nullable|numeric|min:0',
            'tax_code' => 'nullable|string|max:50',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
        ];
    }

    /**
     * Validation rules for stock level and location fields.
     *
     * All fields are optional; quantity thresholds must be non-negative
     * integers and location fields must be valid strings when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function inventoryRules(): array
    {
        return [
            'quantity' => 'nullable|integer|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'max_stock_level' => 'nullable|integer|min:0',
            'reorder_point' => 'nullable|integer|min:0',
            'reorder_quantity' => 'nullable|integer|min:0',
            'lead_time_days' => 'nullable|integer|min:0',
            'warehouse_location' => 'nullable|string|max:255',
            'bin_location' => 'nullable|string|max:255',
        ];
    }

    /**
     * Validation rules for boolean feature and state flags.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function flagRules(): array
    {
        return [
            'is_active' => 'sometimes|boolean',
            'is_purchasable' => 'sometimes|boolean',
            'is_sellable' => 'sometimes|boolean',
            'is_manufactured' => 'sometimes|boolean',
            'is_serialised' => 'sometimes|boolean',
            'is_batch_tracked' => 'sometimes|boolean',
            'is_test' => 'sometimes|boolean',
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
