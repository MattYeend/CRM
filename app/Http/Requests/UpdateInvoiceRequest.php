<?php

namespace App\Http\Requests;

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
        $invoice = $this->route('invoice');

        return [
            'number' => [
                'sometimes',
                'string',
                Rule::unique('invoices', 'number')->ignore($invoice->id),
            ],
            'company_id' => 'nullable|integer|exists:companies,id',
            'contact_id' => 'nullable|integer|exists:contacts,id',
            'issue_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:draft,sent,paid,overdue,cancelled',
            'subtotal' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'total' => 'nullable|numeric',
            'currency' => 'nullable|string|max:8',
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
