<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $contact = $this->route('contact');

        return $this->user()->can('update', $contact);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => 'nullable|integer|exists:companies,id',
            'first_name' => 'sometimes|required|string',
            'last_name' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'job_title' => 'nullable|string',
            'meta' => 'nullable|array',
        ];
    }
}
