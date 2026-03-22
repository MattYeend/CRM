<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Services\AuthenticatedSessionLogService;

class LogAuthenticatedSession
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected AuthenticatedSessionLogService $logService
    ) {
        $this->logService = $logService;
    }

    /**
     * Handle the event.
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
