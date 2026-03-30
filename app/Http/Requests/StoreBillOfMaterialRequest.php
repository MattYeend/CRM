<?php

namespace App\Http\Requests;

use App\Models\BillOfMaterial;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Handles authorisation and validation for storing a new BillOfMaterial.
 *
 * Validates the parent-child part relationship, ensuring the child part
 * exists and differs from the parent, along with quantity, scrap percentage,
 * unit of measure, and optional notes.
 */
class StoreBillOfMaterialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the BillOfMaterial model.
     *
     * @return bool True if the authenticated user may create bills of
     * material.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', BillOfMaterial::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Enforces that the child part exists and is distinct from the parent,
     * that quantity is a positive number, and that all optional fields are
     * of the correct type when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'child_part_id' => [
                'required',
                'exists:parts,id',
                'different:parent_part_id',
            ],
            'quantity' => 'required|numeric|min:0.0001',
            'scrap_percentage' => 'nullable|numeric|min:0|max:100',
            'unit_of_measure' => 'nullable|string',
            'notes' => 'nullable|string',
        ];
    }
}
