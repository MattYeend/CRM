<?php

namespace App\Http\Requests;

use App\Models\PartSerialNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for storing a new PartSerialNumber.
 *
 * Validates the serial number's uniqueness, an optional status constrained
 * to allowed values, batch number, and manufacture and expiry dates.
 */
class StorePartSerialNumberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the PartSerialNumber model.
     *
     * @return bool True if the authenticated user may create part serial
     * numbers.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', PartSerialNumber::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Enforces serial number uniqueness, constrains status to the allowed
     * set, and validates optional batch and date fields. The expiry date
     * must fall after the manufacture date when both are provided.
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
