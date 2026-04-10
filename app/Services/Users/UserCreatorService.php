<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Handles the creation of new User records.
 *
 * Extracts validated data from the request, stamps the creator and
 * creation timestamp, and persists the new User.
 */
class UserCreatorService
{
    /**
     * Create a new user from the validated request data.
     *
     * Sets the created_by and created_at audit fields from the authenticated
     * user before persisting the record. Also handles avatar file upload
     * if provided.
     *
     * Fires the Registered event after creation so that any listeners
     * (e.g. SendWelcomeEmail) are triggered automatically.
     *
     * @param  Request $request Validated request containing user data.
     *
     * @return User The newly created user record.
     */
    public function create(Request $request): User
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')
                ->store('avatars', 'public');
        }

        $data['password'] = Hash::make($data['password']);
        $data['created_by'] = $user->id;

        $newUser = User::create($data);

        event(new Registered($newUser));

        return $newUser;
    }
}
