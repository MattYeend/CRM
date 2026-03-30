<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for storing a new Invoice.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — delegates to relationship, core, and status sub-groups
 *   - relationshipBaseRules — company association
 *   - coreBaseRules — invoice number, dates, and financial totals
 *   - statusBaseRules — invoice status constrained to allowed values
 *   - metaRules — optional meta payload
 */
class StoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the Invoice model.
     *
     * @return bool True if the authenticated user may create invoices.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Invoice::class);
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
     * Aggregate validation rules for all core invoice fields.
     *
     * Merges relationship, core, and status sub-groups into a single
     * base ruleset.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return array_merge(
            $this->relationshipBaseRules(),
            $this->coreBaseRules(),
            $this->statusBaseRules(),
        );
    }

    /**
     * Validation rules for the company association.
     *
     * Ensures company_id, when provided, references an existing company
     * record.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function relationshipBaseRules(): array
    {
        return [
            'company_id' => [
                'nullable',
                'integer',
                Rule::exists('companies', 'id'),
            ],
        ];
    }

    /**
     * Validation rules for core invoice fields.
     *
     * The invoice number must be unique across all invoices. Date and
     * financial fields are optional but constrained to appropriate types
     * when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function coreBaseRules(): array
    {
        return [
            'number' => [
                'required',
                'string',
                Rule::unique('invoices', 'number'),
            ],
            'issue_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'subtotal' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'total' => 'nullable|numeric',
            'currency' => 'nullable|string|max:8',
        ];
    }

    /**
     * Validation rules for the invoice status field.
     *
     * Constrains the value to the set of statuses defined on the Invoice
     * model.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function statusBaseRules(): array
    {
        return [
            'status' => [
                'nullable',
                Rule::in([
                    Invoice::STATUS_DRAFT,
                    Invoice::STATUS_SENT,
                    Invoice::STATUS_PAID,
                    Invoice::STATUS_OVERDUE,
                    Invoice::STATUS_CANCELLED,
                ]),
            ],
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
