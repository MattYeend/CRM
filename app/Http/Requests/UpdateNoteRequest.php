<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $note = $this->route('note');

        return $this->user()->can('update', $note);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            $this->metaRules(),
        );
    }

    /**
     * Base rules
     *
     * @return array
     */
    public function baseRules(): array
    {
        return [
            'body' => 'sometimes|required|string',
        ];
    }

    /**
     * Meta rules
     *
     * @return array
     */
    public function metaRules(): array
    {
        return [
            'meta' => 'nullable|array',
        ];
    }
}
