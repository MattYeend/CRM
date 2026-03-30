<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for storing a new User.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — name, unique email, password confirmation, and avatar
 *   - roleRules — optional role and job title associations
 */
class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the User model.
     *
     * @return bool True if the authenticated user may create users.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
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
     * Validation rules for core user identity and credential fields.
     *
     * Email must be unique across all users. Password must be at least eight
     * characters and confirmed. Avatar is optional but must be a valid image
     * within the permitted formats and size limit when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email'),
            ],
            'password' => 'required|string|min:8|confirmed',
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
