<?php

namespace App\Http\Requests;

use App\Models\Part;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Part::class);
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
     * Base rules
     *
     * @return array
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
     * Type and status rules
     *
     * @return array
     */
    private function typeAndStatusRules(): array
    {
        return [
            'type' => 'required', Rule::in([
                'raw_material',
                'finished_good',
                'consumable',
                'spare_part',
                'sub_assembly',
            ]),
            'status' => 'required', Rule::in([
                'active',
                'discontinued',
                'pending',
                'out_of_stock',
            ]),
        ];
    }

    /**
     * Relationship rules
     *
     * @return array
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
     * Phyiscal rules
     *
     * @return array
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
     * Pricing rules
     *
     * @return array
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
     * Inventory rules
     *
     * @return array
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
     * Flag rules
     *
     * @return array
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
     * Meta rules
     *
     * @return array
     */
    private function metaRules(): array
    {
        return [
            'meta' => 'nullable|array',
        ];
    }
}
