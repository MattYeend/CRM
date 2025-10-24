<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserCreator
{
    /**
     * Create a new user from request data.
     *
     * @param Request $request
     */
    public function create(Request $request): User
    {
        $data = $request->validate($this->storeValidationRules());

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')
                ->store('avatars', 'public');
        }

        $data['password'] = Hash::make($data['password']);

        return User::create($data);
    }

    /**
     * Get validation rules for storing a user.
     *
     * @return array
     */
    private function storeValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|file|mimes:jpeg,png,gif,webp|max:5120',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ];
    }
}
