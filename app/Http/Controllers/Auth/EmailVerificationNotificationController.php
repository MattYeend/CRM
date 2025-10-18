<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\EmailVerificationNotificationLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Declare a protected propert to hold the
     * EmailVerificationNotificationLogService instance
     */
    protected EmailVerificationNotificationLogService $logger;

    /**
     * Constructor for the controller
     *
     * @param EmailVerificationNotificationLogService $logger
     * An instance of the EmailVerificationNotificationLogService used for
     * logging password-related activities
     */
    public function __construct(EmailVerificationNotificationLogService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->logger->verify($user, auth()->id());

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
