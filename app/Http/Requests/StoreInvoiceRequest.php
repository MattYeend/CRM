<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Invoice::class);
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
     * Main base rules
     *
     * @return array
     */
    public function mainBaseRules(): array
    {
        return [
            'number' => [
                'required',
                'string',
                Rule::unique('invoices', 'number'),
            ],
            'company_id' => 'nullable|integer|exists:companies,id',
            'contact_id' => 'nullable|integer|exists:contacts,id',
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
    public function statusBaseRules(): array
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
     * Base rules
     *
     * @return array
     */
    private function baseRules(): array
    {
        return array_merge(
            $this->mainBaseRules(),
            $this->statusBaseRules(),
        );
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
