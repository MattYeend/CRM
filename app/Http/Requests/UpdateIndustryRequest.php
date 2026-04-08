<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Authorises and validates a request to update an existing industry.
 *
 * Authorisation is delegated to the 'update' policy for the Industry model.
 * Validation ensures the industry name is present, a string, within the
 * character limit, and unique across all industries — ignoring the current
 * industry's own record to allow the name to be saved unchanged.
 *
 * Example usage:
 * ```php
 * // In a controller method:
 * public function update(UpdateIndustryRequest $request, Industry $industry): RedirectResponse
 * {
 *     $validated = $request->validated();
 *     $industry->update($validated);
 * }
 * ```
 */
class UpdateIndustryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'update' policy for the Industry model.
     *
     * @return bool True if the authenticated user may update this industry.
     */
    public function authorize(): bool
    {
        $industry = $this->route('industry');

        return $this->user()->can('update', $industry);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * - name: Required. Must be a string no longer than 255 characters
     *       and unique within the industries table, ignoring the current
     *       industry record to permit saving without changing the name.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:industries,name,' . $this->route('industry')->id,
        ];
    }
}
