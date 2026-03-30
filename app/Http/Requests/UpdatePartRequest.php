<?php

namespace App\Http\Requests;

use App\Models\Part;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $part = $this->route('part');

        return $this->user()->can('update', $part);
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
     * Base rules
     *
     * @return array
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
     * Type rules
     *
     * @return array
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
     * Status rules
     *
     * @return array
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
     * Relationship rules
     *
     * @return array
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
     * Physical rules
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
            'price' => 'sometimes|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
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
