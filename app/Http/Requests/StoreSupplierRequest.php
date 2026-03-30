<?php

namespace App\Http\Requests;

use App\Models\Supplier;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for storing a new Supplier.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — required name and optional unique supplier code
 *   - infoRules — email, phone, and website contact fields
 *   - addressRules — full postal address fields
 *   - priceRules — currency, payment terms, and tax number
 *   - contactRules — primary contact name, email, and phone
 *   - metaRules — active flag, notes, and optional meta payload
 */
class StoreSupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the Supplier model.
     *
     * @return bool True if the authenticated user may create suppliers.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Supplier::class);
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
     * Name is required; code is optional but must be unique across all
     * suppliers when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                Rule::unique('suppliers', 'code'),
            ],
        ];
    }

    /**
     * Validation rules for general supplier contact information.
     *
     * All fields are optional but must be of the correct type when provided.
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
     * All fields are optional; country must be a two-character ISO code
     * when provided.
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
