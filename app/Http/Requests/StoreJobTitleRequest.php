<?php

namespace App\Http\Requests;

use App\Models\JobTitle;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for storing a new JobTitle.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — title uniqueness, short code, and optional group
 *   - metaRules — optional meta payload
 */
class StoreJobTitleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the JobTitle model.
     *
     * @return bool True if the authenticated user may create job titles.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', JobTitle::class);
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
     * The title must be unique across all job titles. Short code is required
     * and group is optional.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('job_titles', 'title'),
            ],
            'short_code' => 'required|string|max:255',
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
