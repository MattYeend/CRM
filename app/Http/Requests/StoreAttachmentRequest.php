<?php

namespace App\Http\Requests;

use App\Models\Attachment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttachmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Attachment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            $this->attchmentRules(),
        );
    }

    /**
     * Base rules
     *
     * @return array
     */
    private function baseRules(): array
    {
        return [
            'file' => 'required|file|max:10000',
        ];
    }

    /**
     * Attachmetn rules
     *
     * @return array
     */
    private function attchmentRules(): array
    {
        return [
            'attachable_type' => [
                'required',
                Rule::in(['deal', 'contact', 'company', 'task', 'user']),
            ],
            'attachable_id' => ['required', 'integer'],
        ];
    }
}
