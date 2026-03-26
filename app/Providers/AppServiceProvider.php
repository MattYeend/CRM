<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Deal;
use App\Models\Order;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Empty register method
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'company' => Company::class,
            'deal' => Deal::class,
            'task' => Task::class,
            'user' => User::class,
        ]);

        Cashier::useCustomerModel(User::class);
        Cashier::calculateTaxes();

        if (app()->environment('production', 'staging', 'qa')) {
            $this->preventDestructiveCommands();
        }

        Event::listen('cashier.payment_succeeded', function ($payload) {
            Order::where(
                'stripe_payment_intent',
                $payload['data']['object']['id']
            )->update(['status' => 'paid']);
        });
    }

    /**
     * Prevent destructive commands from running.
     */
    protected function preventDestructiveCommands()
    {
        $destructiveCommands = [
            'migrate:fresh', // Drops all tables
            'migrate:reset', // Rolls back all migrations
            'migrate:rollback', // Rolls back a batch of migrations
            'db:wipe', // Drops all databases
        ];

        foreach ($destructiveCommands as $command) {
            Artisan::command($command, function () use ($command) {
                /** @var Command $this */
                $this->error(
                    "This '{$command}' command is disabled in this 
                        environment for safety."
                );
            });
        }
    }
}
