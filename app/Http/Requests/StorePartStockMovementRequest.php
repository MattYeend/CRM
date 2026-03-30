<?php

namespace App\Http\Requests;

use App\Models\PartStockMovement;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Handles authorisation and validation for storing a new PartStockMovement.
 *
 * Validates the movement type against a fixed set of allowed values,
 * enforces a non-zero quantity, and accepts optional reference, notes,
 * and meta fields.
 */
class StorePartStockMovementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the PartStockMovement model.
     *
     * @return bool True if the authenticated user may create part stock
     * movements.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', PartStockMovement::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Enforces a valid movement type, a non-zero integer quantity, and
     * appropriate types for all optional fields when provided.
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
