<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for updating an existing Supplier.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — supplier name and optional unique code
 *   - infoRules — email, phone, and website fields
 *   - addressRules — postal address fields
 *   - priceRules — currency, payment terms, and tax number
 *   - contactRules — primary contact name, email, and phone
 *   - metaRules — active flag, notes, and optional metadata
 */
class UpdateSupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound supplier and delegates to the 'update' policy.
     *
     * @return bool True if the authenticated user may update this supplier.
     */
    public function authorize(): bool
    {
        $supplier = $this->route('supplier');

        return $this->user()->can('update', $supplier);
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
            $this->infoRules(),
            $this->addressRules(),
            $this->priceRules(),
            $this->contactRules(),
            $this->metaRules(),
        );
    }

    /**
     * Validation rules for core supplier identity fields.
     *
     * Name is optional for updates; code remains unique across all suppliers
     * excluding the current model.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
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
     * Validation rules for general supplier contact information.
     *
     * All fields are optional but must conform to the correct type
     * when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
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
     * Validation rules for supplier postal address fields.
     *
     * All fields are optional; country must be a two-character
     * ISO code when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
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
     * Validation rules for pricing, payment, and tax fields.
     *
     * Currency must be a three-character ISO code when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
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
     * Validation rules for the primary contact person fields.
     *
     * All fields are optional but must be of the correct type when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
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
     * Validation rules for active status, notes, and metadata fields.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
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
