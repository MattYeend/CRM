<?php

namespace App\Http\Requests;

use App\Models\Part;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for storing a new Part.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — core part identity fields including SKU and unit of measure
 *   - typeAndStatusRules — part type and status constrained to allowed values
 *   - relationshipRules — product, category, and supplier associations
 *   - physicalRules — dimensional and material attributes
 *   - pricingRules — price, cost, currency, tax, and discount fields
 *   - inventoryRules — stock levels, reorder thresholds, and warehouse location
 *   - flagRules — boolean feature and state flags
 *   - metaRules — optional meta payload
 */
class StorePartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the Part model.
     *
     * @return bool True if the authenticated user may create parts.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Part::class);
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
            $this->typeAndStatusRules(),
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
     * SKU must be unique across all parts. Name, description, and unit of
     * measure are required; all other fields are optional.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'sku' => 'required|string|max:255|unique:parts,sku',
            'part_number' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'unit_of_measure' => 'required|string|max:50',
        ];
    }

    /**
     * Validation rules for part type and status fields.
     *
     * Constrains both values to their respective allowed sets defined on
     * the Part model.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function typeAndStatusRules(): array
    {
        return [
            'type' => 'required', Rule::in(Part::PART_TYPES),
            'status' => 'required', Rule::in(Part::ACTIVE_PART_STATUS),
        ];
    }

    /**
     * Validation rules for product, category, and supplier associations.
     *
     * Product association is required; category and supplier are optional
     * but must reference existing records when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function relationshipRules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'category_id' => 'nullable|exists:part_categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ];
    }

    /**
     * Validation rules for physical dimension and material fields.
     *
     * All fields are optional but must be non-negative numerics or strings
     * of the correct type when provided.
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
     * Price and currency are required; cost price, tax rate, tax code, and
     * discount percentage are optional but constrained to appropriate types
     * and ranges when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function pricingRules(): array
    {
        return [
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'tax_rate' => 'nullable|numeric|min:0',
            'tax_code' => 'nullable|string|max:50',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
        ];
    }

    /**
     * Validation rules for stock level and warehouse location fields.
     *
     * All fields are optional; quantity thresholds must be non-negative
     * integers and location fields must be strings when provided.
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
     * Validation rules for boolean feature and state flag fields.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function flagRules(): array
    {
        return [
            'is_active' => 'boolean',
            'is_purchasable' => 'boolean',
            'is_sellable' => 'boolean',
            'is_manufactured' => 'boolean',
            'is_serialised' => 'boolean',
            'is_batch_tracked' => 'boolean',
            'is_test' => 'boolean',
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
