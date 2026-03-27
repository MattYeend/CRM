<?php

namespace App\Http\Requests;

use App\Models\PartStockMovement;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePartStockMovementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', PartStockMovement::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:in,out,adjustment,transfer,return',
            'quantity' => 'required|integer|not_in:0',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'meta' => 'nullable|array',
        ];
    }
}
