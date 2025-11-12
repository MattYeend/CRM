<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\RegisteredUserLogService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Declare a protected propert to hold the
     * RegisteredUserLogService instance
     *
     * @var RegisteredUserLogService
     */
    protected RegisteredUserLogService $logger;

    /**
     * Constructor for the controller
     *
     * @param RegisteredUserLogService $logger
     * An instance of the RegisteredUserLogService used for logging
     * password-related activities
     */
    public function __construct(RegisteredUserLogService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'
                .User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();
        $this->logger->register($user, auth()->id());

        return to_route('dashboard');
    }
}
