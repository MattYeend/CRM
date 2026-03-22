<?php

namespace App\Listeners;

use App\Services\AuthenticatedSessionLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected AuthenticatedSessionLogService $logService
    )
    {
        $this->logService = $logService;
    }

    /**
     * Handle the event.
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
