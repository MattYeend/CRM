<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBillOfMaterialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $billOfMaterial = $this->route('billOfMaterial');

        return $this->user()->can('update', $billOfMaterial);
    }

    /**
     * Get the validation rules that apply to the request.
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
