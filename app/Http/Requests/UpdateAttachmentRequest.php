<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttachmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $attachment = $this->route('attachment');

        return $this->user()->can('update', $attachment);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => 'nullable|file|max:10000',
            'attachable_type' => 'nullable|string|required_with:attachable_id',
            'attachable_id' => 'nullable|integer|required_with:attachable_type',
            'uploaded_by' => 'nullable|integer|exists:users,id',
        ];
    }
}
