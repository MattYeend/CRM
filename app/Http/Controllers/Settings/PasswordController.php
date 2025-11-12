<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\PasswordLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    /**
     * Declare a protected propert to hold the PasswordLogService instance
     *
     * @var PasswordLogService
     */
    protected PasswordLogService $logger;

    /**
     * Constructor for the controller
     *
     * @param PasswordLogService $logger
     * An instance of the PasswordLogService used for logging
     * password-related activities
     */
    public function __construct(PasswordLogService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Show the user's password settings page.
     */
    public function edit(): Response
    {
        return Inertia::render('settings/Password');
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();
        $this->logger->update($user, auth()->id());

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return back();
    }
}
