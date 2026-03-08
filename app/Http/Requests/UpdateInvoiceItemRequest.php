<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $invoiceItem = $this->route('invoice_item');

        return $this->user()->can('update', $invoiceItem);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'description' => 'sometimes|required|string',
            'quantity' => 'nullable|integer|min:1',
            'unit_price' => 'nullable|numeric',
            'line_total' => 'nullable|numeric',
            'meta' => 'nullable|array',
        ];
    }
}
