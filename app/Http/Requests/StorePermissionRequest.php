<?php

namespace App\Http\Requests;

use App\Models\Permission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for storing a new Permission.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — unique permission name
 *   - metaRules — optional human-readable label
 */
class StorePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the Permission model.
     *
     * @return bool True if the authenticated user may create permissions.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Permission::class);
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
     * Validation rules for the permission name.
     *
     * The name must be unique across all permissions.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('permissions', 'name'),
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
