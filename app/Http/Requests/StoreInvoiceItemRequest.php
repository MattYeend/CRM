<?php

namespace App\Http\Requests;

use App\Models\InvoiceItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for storing a new InvoiceItem.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — invoice and product associations, description, quantity,
 *      and unit price
 *   - metaRules — optional line total and meta payload
 */
class StoreInvoiceItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the InvoiceItem model.
     *
     * @return bool True if the authenticated user may create invoice items.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', InvoiceItem::class);
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
     * Ensures invoice_id references an existing invoice, product_id
     * optionally references an existing product, and that description,
     * quantity, and unit price are present and valid.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'invoice_id' => [
                'required',
                'integer',
                Rule::exists('invoices', 'id'),
            ],
            'product_id' => [
                'nullable',
                'integer',
                Rule::exists('products', 'id'),
            ],
            'description' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric',
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
