<?php

namespace App\Listeners;

use App\Services\AuthenticatedSessionLogService;
use Illuminate\Auth\Events\Login;

/**
 * Listens for successful login events and writes audit log entries for
 * both the login action and its successful outcome.
 *
 * Delegates all logging to the AuthenticatedSessionLogService, keeping
 * audit concerns out of the listener itself.
 */
class LogAuthenticatedSession
{
    /**
     * Inject the session log service into the listener.
     *
     * @param  AuthenticatedSessionLogService $logService Handles audit logging
     * for authentication session events.
     */
    public function __construct(
        protected AuthenticatedSessionLogService $logService
    ) {
        $this->logService = $logService;
    }

    /**
     * Handle the login event.
     *
     * Writes both a login and a login success audit log entry against the
     * authenticated user.
     *
     * @param  Login $event The login event carrying the authenticated user.
     *
     * @return void
     */
    public function handle(Login $event): void
    {
        $this->logService->login(
            $event->user,
            $event->user->id,
        );
        $this->logService->loginSuccess(
            $event->user,
            $event->user->id,
        );
    }
}
