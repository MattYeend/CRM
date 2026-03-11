<?php

namespace App\Http\Requests;

use App\Models\Note;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Note::class);
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
            $this->noteRules(),
            $this->metaRules(),
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
            'user_id' => 'nullable|integer|exists:users,id',
            'body' => 'required|string',
        ];
    }

    /**
     * Attachmetn rules
     *
     * @return array
     */
    private function noteRules(): array
    {
        return [
            'notable_type' => [
                'required',
                Rule::in(['deal', 'contact', 'company', 'task', 'user']),
            ],
            'notable_id' => [
                'required',
                'integer',
                'required_with:notable_type',
            ],
        ];
    }

    /**
     * Meta rules
     *
     * @return array
     */
    private function metaRules(): array
    {
        return [
            'meta' => 'nullable|array',
        ];
    }
}
