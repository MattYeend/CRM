<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for updating an existing User.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — name, email, password, phone, and avatar
 *   - roleRules — optional role and job title associations
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound user and delegates to the 'update' policy.
     *
     * @return bool True if the authenticated user may update this user.
     */
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $this->user()->can('update', $user);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Merges base and role rule groups into a single ruleset.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            $this->roleRules(),
        );
    }

    /**
     * Validation rules for core user fields.
     *
     * Email must remain unique except for the user being updated. Password
     * is optional, but must be confirmed and at least 8 characters if provided.
     * Avatar is optional but must be a valid image within allowed formats
     * and size.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        $user = $this->route('user');

        return [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($user),
            ],
            'password' => 'sometimes|string|min:8|confirmed',
            'phone' => 'nullable|string|max:25',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }

    /**
     * Validation rules for role and job title associations.
     *
     * Both fields are optional but must reference existing records when
     * provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function roleRules(): array
    {
        return [
            'role_id' => [
                'sometimes',
                'integer',
                Rule::exists('roles', 'id'),
            ],
            'job_title_id' => [
                'sometimes',
                'integer',
                Rule::exists('job_titles', 'id'),
            ],
        ];
    }
}
