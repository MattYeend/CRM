<?php

namespace App\Listeners;

use App\Services\AuthenticatedSessionLogService;
use Illuminate\Auth\Events\Failed;

/**
 * Listens for failed login events and writes an audit log entry when a
 * resolved user is present on the event.
 *
 * Delegates all logging to the AuthenticatedSessionLogService, keeping
 * audit concerns out of the listener itself. Events without a resolved
 * user are silently ignored.
 */
class LogFailedLogin
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
     * Handle the failed login event.
     *
     * Writes a failed login audit log entry against the resolved user.
     * Does nothing if the event does not carry a user instance.
     *
     * @param  Failed $event The failed login event, optionally carrying the
     * resolved user.
     *
     * @return void
     */
    public function handle(Failed $event): void
    {
        if ($event->user) {
            $this->logService->failedLogin(
                $event->user,
                $event->user->id
            );
        }
    }
}
