<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Handles authorisation and validation for updating an existing InvoiceItem.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — description, quantity, and unit price fields
 *   - metaRules — optional line total and meta payload
 */
class UpdateInvoiceItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound invoice item and delegates to the 'update'
     * policy.
     *
     * @return bool True if the authenticated user may update this invoice
     * item.
     */
    public function authorize(): bool
    {
        $invoiceItem = $this->route('invoice_item');

        return $this->user()->can('update', $invoiceItem);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Merges base and meta rule groups into a single ruleset.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            $this->metaRules(),
        );
    }

    /**
     * Validation rules for core invoice item fields.
     *
     * All fields are optional on update but constrained to appropriate types
     * and ranges when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'description' => 'sometimes|string|max:255',
            'quantity' => 'nullable|integer|min:1',
            'unit_price' => 'nullable|numeric',
        ];
    }

    /**
     * Validation rules for optional line total and metadata fields.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function metaRules(): array
    {
        return [
            'line_total' => 'nullable|numeric',
            'meta' => 'nullable|array',
        ];
    }
}
