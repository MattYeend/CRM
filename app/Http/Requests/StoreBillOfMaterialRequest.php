<?php

namespace App\Http\Requests;

use App\Models\BillOfMaterial;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBillOfMaterialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', BillOfMaterial::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'child_part_id' => 'required|exists:parts,id|different:parent_part_id',
            'quantity' => 'required|numeric|min:0.0001',
            'unit_of_measure' => 'nullable|string',
            'notes' => 'nullable|string',
        ];
    }
}
