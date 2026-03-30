<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Handles authorisation and validation for updating an existing
 * BillOfMaterial.
 *
 * Validates optional updates to quantity, scrap percentage, unit of measure,
 * and notes. All fields are partial-update safe and only applied when present
 * in the request.
 */
class UpdateBillOfMaterialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound bill of material and delegates to the
     * 'update' policy.
     *
     * @return bool True if the authenticated user may update this bill of
     * material.
     */
    public function authorize(): bool
    {
        $billOfMaterial = $this->route('billOfMaterial');

        return $this->user()->can('update', $billOfMaterial);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Enforces that quantity and scrap percentage are positive numbers within
     * their valid ranges when provided, and that all other fields are of the
     * correct type.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quantity' => 'sometimes|numeric|min:0.0001',
            'scrap_percentage' => 'sometimes|numeric|min:0|max:100',
            'unit_of_measure' => 'nullable|string',
            'notes' => 'nullable|string',
        ];
    }
}
