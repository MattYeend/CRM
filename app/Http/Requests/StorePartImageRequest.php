<?php

namespace App\Http\Requests;

use App\Models\PartImage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePartImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', PartImage::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'alt' => 'nullable|string|max:255',
            'is_primary' => 'boolean',
            'sort_order' => 'integer|min:0',
            'is_test' => 'boolean',
            'meta' => 'nullable|array',
        ];
    }
}
