<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Handles authorisation and validation for updating an existing Pipeline.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — optional pipeline name
 *   - metaRules — optional description and default flag
 */
class UpdatePipelineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound pipeline and delegates to the 'update' policy.
     *
     * @return bool True if the authenticated user may update this pipeline.
     */
    public function authorize(): bool
    {
        $pipeline = $this->route('pipeline');

        return $this->user()->can('update', $pipeline);
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
     * Validation rules for the pipeline name field.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
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
