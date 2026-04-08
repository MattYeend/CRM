<?php

namespace App\Http\Requests;

use App\Models\Industry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Authorises and validates a request to create a new industry.
 *
 * Authorisation is delegated to the 'create' policy for the Industry model.
 * Validation ensures the industry name is present, a string, within the
 * character limit, and unique across all industries.
 *
 * Example usage:
 * ```php
 * // In a controller method:
 * public function store(StoreIndustryRequest $request): RedirectResponse
 * {
 *     $validated = $request->validated();
 *     Industry::create($validated);
 * }
 * ```
 */
class StoreIndustryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the Industry model.
     *
     * @return bool True if the authenticated user may create industries.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Industry::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * - name: Required. Must be a string no longer than 255 characters
     *       and unique within the industries table.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:industries,name',
        ];
    }
}
