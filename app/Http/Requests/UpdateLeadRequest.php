<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for updating an existing Lead.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — core lead identity and contact fields
 *   - metaRules — owner and assignee associations, and optional meta payload
 */
class UpdateLeadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound lead and delegates to the 'update' policy.
     *
     * @return bool True if the authenticated user may update this lead.
     */
    public function authorize(): bool
    {
        $lead = $this->route('lead');

        return $this->user()->can('update', $lead);
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
     * Validation rules for core lead identity and contact fields.
     *
     * All fields are optional on update but constrained to appropriate types
     * and lengths when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'source' => 'nullable|string|max:255',
        ];
    }

    /**
     * Validation rules for owner, assignee, and metadata fields.
     *
     * Ensures owner_id and assigned_to, when provided, reference existing
     * user records.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function metaRules(): array
    {
        return [
            'owner_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'meta' => 'nullable|array',
        ];
    }
}
