<?php

namespace App\Http\Requests;

use App\Models\PartSerialNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for updating an existing
 * PartSerialNumber.
 *
 * Validation rules ensure the serial number remains unique when updated,
 * constrain status to the allowed set, and validate optional batch and
 * date fields, including enforcing chronological consistency.
 */
class UpdatePartSerialNumberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound serial number and delegates to the
     * 'update' policy.
     *
     * @return bool True if the authenticated user may update this part
     * serial number.
     */
    public function authorize(): bool
    {
        $partSerialNumber = $this->route('serialNumber');

        return $this->user()->can('update', $partSerialNumber);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Applies conditional validation for updatable fields, ensuring uniqueness,
     * valid status values, and correct date relationships when provided.
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
