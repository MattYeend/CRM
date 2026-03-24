<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');

        return $this->user()->can('update', $invoice);
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
            $this->relationshipBaseRules(),
            $this->coreBaseRules(),
            $this->statusBaseRules(),
        );
    }

    /**
     * Relationship base rules
     *
     * @return array
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
     * Core base rules
     *
     * @return array
     */
    private function coreBaseRules(): array
    {
        $invoice = $this->route('invoice');

        return [
            'number' => [
                'sometimes',
                'string',
                Rule::unique('invoices', 'number')->ignore($invoice),
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
     * Status base rules
     *
     * @return array
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
