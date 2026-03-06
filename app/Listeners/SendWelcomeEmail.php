<?php

namespace App\Listeners;

use App\Mail\WelcomeEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        // Currently Empty
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        Mail::to($event->user->email)
            ->queue(new WelcomeEmail($event->user));
    }
}
