<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    /**
     * Get the validation rules that apply to the request.
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
     * Base rules
     *
     * @return array
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
     * Role rules
     *
     * @return array
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
