<?php

namespace App\Http\Requests;

use App\Models\Lead;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for storing a new Lead.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — core lead identity and contact fields
 *   - metaRules — owner and assignee associations, and optional meta payload
 */
class StoreLeadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the Lead model.
     *
     * @return bool True if the authenticated user may create leads.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Lead::class);
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
     * Title, first name, and last name are required; email, phone, and
     * source are optional but constrained to appropriate types and lengths
     * when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
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
