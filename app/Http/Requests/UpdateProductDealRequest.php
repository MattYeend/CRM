<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductDealRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $productDeals = $this->route('product_deal');

        return $this->user()->can('update', $productDeals);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            $this->priceRules(),
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
            $this->productRules(),
            $this->dealRules(),
        );
    }
    /**
     * Product rules
     *
     * @return array
     */
    private function productRules(): array
    {
        $productDeals = $this->route('product_deal');

        return [
            'product_id' => [
                'nullable',
                'integer',
                'exists:products,id',
                Rule::unique(
                    'product_deals',
                    'product_id'
                )->ignore($productDeals->product_id),
            ],
        ];
    }
    /**
     * Deal rules
     *
     * @return array
     */
    private function dealRules(): array
    {
        $productDeals = $this->route('product_deal');

        return [
            'deal_id' => [
                'required',
                'integer',
                'exists:deals,id',
                Rule::unique(
                    'product_deals',
                    'deal_id'
                )->ignore($productDeals->deal_id),
            ],
        ];
    }

    /**
     * Price rules
     *
     * @return array
     */
    private function priceRules(): array
    {
        return [
            'quantity' => 'nullable|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'total_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
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
