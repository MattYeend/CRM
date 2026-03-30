<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for updating an existing JobTitle.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — title uniqueness, short code, and optional group
 *   - metaRules — optional meta payload
 */
class UpdateJobTitleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound job title and delegates to the 'update' policy.
     *
     * @return bool True if the authenticated user may update this job title.
     */
    public function authorize(): bool
    {
        $jobTitle = $this->route('job_title');

        return $this->user()->can('update', $jobTitle);
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
     * Validation rules for core job title fields.
     *
     * The title uniqueness check ignores the current job title to allow
     * updates that do not change the title. Short code and group are
     * optional on update.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        $jobTitle = $this->route('job_title');

        return [
            'title' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('job_titles', 'title')->ignore($jobTitle),
            ],
            'short_code' => 'sometimes|string|max:255',
            'group' => 'nullable|string|max:255',
        ];
    }

    /**
     * Validation rules for optional metadata fields.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function metaRules(): array
    {
        return [
            'meta' => 'nullable|array',
        ];
    }
}
