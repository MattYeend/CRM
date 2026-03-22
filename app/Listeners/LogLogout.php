<?php

namespace App\Listeners;

use App\Services\AuthenticatedSessionLogService;
use Illuminate\Auth\Events\Logout;

class LogLogout
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
    public function handle(Logout $event): void
    {
        if ($event->user) {
            $this->logService->logout(
                $event->user,
                $event->user->id
            );
        }
    }
}
