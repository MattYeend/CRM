<?php

namespace App\Http\Requests;

use App\Models\Pipeline;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Handles authorisation and validation for storing a new Pipeline.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — required pipeline name
 *   - metaRules — optional description and default flag
 */
class StorePipelineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the Pipeline model.
     *
     * @return bool True if the authenticated user may create pipelines.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Pipeline::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Merges base and meta rule groups into a single ruleset.
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
     * Validation rules for the pipeline name.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    /**
     * Validation rules for optional pipeline configuration fields.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function metaRules(): array
    {
        return [
            'description' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ];
    }
}
