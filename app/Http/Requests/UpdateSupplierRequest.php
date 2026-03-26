<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $supplier = $this->route('supplier');

        return $this->user()->can('update', $supplier);
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
            $this->infoRules(),
            $this->addressRules(),
            $this->priceRules(),
            $this->contactRules(),
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
        $supplier = $this->route('supplier');

        return [
            'name' => 'sometimes|string|max:255',
            'code' => [
                'nullable',
                'string',
                Rule::unique('suppliers', 'code')->ignore($supplier),
            ],
        ];
    }

    /**
     * Info rules
     *
     * @return array
     */
    private function infoRules(): array
    {
        return [
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'website' => 'nullable|url',
        ];
    }

    /**
     * Address rules
     *
     * @return array
     */
    private function addressRules(): array
    {
        return [
            'address_line_1' => 'nullable|string',
            'address_line_2' => 'nullable|string',
            'city' => 'nullable|string',
            'county' => 'nullable|string',
            'postcode' => 'nullable|string',
            'country' => 'nullable|string|size:2',
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
            'currency' => 'nullable|string|size:3',
            'payment_terms' => 'nullable|string',
            'tax_number' => 'nullable|string',
        ];
    }

    /**
     * Contact rules
     *
     * @return array
     */
    private function contactRules(): array
    {
        return [
            'contact_name' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
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
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'meta' => 'nullable|array',
        ];
    }
}
