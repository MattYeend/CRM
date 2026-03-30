<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for updating an existing Permission.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — unique permission name with current model ignored
 *   - metaRules — optional human-readable label
 */
class UpdatePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound permission and delegates to the 'update' policy.
     *
     * @return bool True if the authenticated user may update this permission.
     */
    public function authorize(): bool
    {
        $permission = $this->route('permission');

        return $this->user()->can('update', $permission);
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
     * Validation rules for the permission name field.
     *
     * Ensures the name remains unique across permissions, excluding the
     * current model being updated.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        $permission = $this->route('permission');

        return [
            'name' => [
                'sometimes',
                'string',
                Rule::unique('permissions', 'name')->ignore($permission),
            ],
        ];
    }

    /**
     * Validation rules for the optional human-readable label.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function metaRules(): array
    {
        return [
            'label' => 'nullable|string',
        ];
    }
}
