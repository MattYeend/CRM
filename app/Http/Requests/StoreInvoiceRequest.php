<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'number' => $this->numberRule(),
            'company_id' => 'nullable|integer|exists:companies,id',
            'contact_id' => 'nullable|integer|exists:contacts,id',
            'created_by' => 'nullable|integer|exists:users,id',
            'issue_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:draft,sent,paid,overdue,cancelled',
            'subtotal' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'total' => 'nullable|numeric',
            'currency' => 'nullable|string|max:8',
            'meta' => 'nullable|array',
        ];
    }

    /**
     * Create number rule
     *
     * @param Invoice $invoice
     *
     * @return array
     */
    private function numberRule(?Invoice $invoice = null): array
    {
        $uniqueRule = \Illuminate\Validation\Rule::unique('invoices', 'number');

        if ($invoice) {
            $uniqueRule = $uniqueRule->ignore($invoice->id);
            return ['sometimes', 'required', 'string', $uniqueRule];
        }

        return ['required', 'string', $uniqueRule];
    }
}
