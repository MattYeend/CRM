<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for updating an existing PartImage.
 *
 * Validation rules ensure the uploaded image (when provided) meets file
 * type and size constraints, remains unique where applicable, and that
 * optional display, ordering, and metadata fields are correctly typed.
 */
class UpdatePartImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound part image and delegates to the 'update' policy.
     *
     * @return bool True if the authenticated user may update this part image.
     */
    public function authorize(): bool
    {
        $partImage = $this->route('partImage');

        return $this->user()->can('update', $partImage);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Applies conditional validation for the image field and validates all
     * optional display and metadata fields when present.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $partImage = $this->route('partImage');

        return [
            'image' => [
                'sometimes',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
                Rule::unique('partImages', 'image')->ignore($partImage),
            ],
            'alt' => 'nullable|string|max:255',
            'is_primary' => 'boolean',
            'sort_order' => 'integer|min:0',
            'is_test' => 'boolean',
            'meta' => 'nullable|array',
        ];
    }
}
