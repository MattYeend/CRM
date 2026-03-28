<?php

namespace App\Http\Requests;

use App\Models\PartSerialNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePartSerialNumberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $partSerialNumber = $this->route('serialNumber');

        return $this->user()->can('update', $partSerialNumber);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $serialNumber = $this->route('serialNumber');

        return [
            'serial_number' => [
                'sometimes',
                'string',
                Rule::unique('part_serial_numbers', 'serial_number')
                    ->ignore($serialNumber),
            ],
            'status' => [
                'sometimes',
                Rule::in(PartSerialNumber::STATUSES),
            ],
            'batch_number' => 'nullable|string',
            'manufactured_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:manufactured_at',
            'meta' => 'nullable|array',
        ];
    }
}
