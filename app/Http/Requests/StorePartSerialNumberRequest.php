<?php

namespace App\Http\Requests;

use App\Models\PartSerialNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePartSerialNumberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', PartSerialNumber::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'serial_number' => [
                'required',
                'string',
                Rule::unique('part_serial_numbers', 'serial_number'),
            ],
            'status' => [
                'sometimes',
                Rule::in(PartSerialNumber::STATUSES),
            ],
            'batch_number' => 'nullable|string',
            'manufactured_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:manufactured_at',
        ];
    }
}
