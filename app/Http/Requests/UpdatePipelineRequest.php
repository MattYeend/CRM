<?php

namespace App\Http\Requests;

use App\Models\Pipeline;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePipelineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $pipeline = $this->route('pipeline');

        return $this->user()->can('update', $pipeline);
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
            'name' => 'sometimes|string|max:255',
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
            'description' => 'nullable|string',
            'is_default' => [
                'nullable',
                Rule::in([
                    Pipeline::IS_DEFAULT,
                    Pipeline::NOT_DEFAULT,
                ]),
            ],
        ];
    }
}
