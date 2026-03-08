<?php

namespace App\Http\Requests;

use App\Models\Contact;
use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Contact::class);
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
            'first_name' => 'required|string',
            'last_name' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'job_title' => 'nullable|string',
            'meta' => 'nullable|array',
        ];
    }
}
