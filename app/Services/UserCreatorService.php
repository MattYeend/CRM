<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserCreatorService
{
    /**
     * Create a new user from request data.
     *
     * @param Request $request
     */
    public function create(Request $request): User
    {
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')
                ->store('avatars', 'public');
        }

        $data['password'] = Hash::make($data['password']);

        return User::create($data);
    }
}
