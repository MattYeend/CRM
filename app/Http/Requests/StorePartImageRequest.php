<?php

namespace App\Http\Requests;

use App\Models\PartImage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Handles authorisation and validation for storing a new PartImage.
 *
 * Validates the part association, the uploaded image file and its format
 * constraints, display metadata such as alt text and sort order, and
 * optional flags for primary image designation and test records.
 */
class StorePartImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the PartImage model.
     *
     * @return bool True if the authenticated user may create part images.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', PartImage::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Enforces that the part exists, the uploaded file is a valid image
     * within the permitted formats and size limit, and that all optional
     * display and flag fields are of the correct type when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'part_id' => 'required|exists:parts,id',
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'alt' => 'nullable|string|max:255',
            'is_primary' => 'boolean',
            'sort_order' => 'integer|min:0',
            'is_test' => 'boolean',
            'meta' => 'nullable|array',
        ];
    }
}
