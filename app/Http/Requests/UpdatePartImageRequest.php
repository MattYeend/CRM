<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePartImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $partImage = $this->route('partImage');

        return $this->user()->can('update', $partImage);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $partImage = $this->route('partImage');
        return [
            'image' => [
                'sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120',
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
