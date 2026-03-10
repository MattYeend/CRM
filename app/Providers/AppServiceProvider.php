<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;

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
        if (app()->environment('production', 'staging', 'qa')) {
            $this->preventDestructiveCommands();
        }

        Relation::morphMap([
            'deal' => Deal::class,
            'contact' => Contact::class,
            'company' => Company::class,
            'user' => User::class,
        ]);
    }

    /**
     * Prevent destructive commands from running.
     */
    protected function preventDestructiveCommands()
    {
        $destructiveCommands = [
            'migrate:fresh',    // Drops all tables
            'migrate:reset',    // Rolls back all migrations
            'migrate:rollback', // Rolls back a batch of migrations
            'db:wipe',          // Drops all databases
        ];

        foreach ($destructiveCommands as $command) {
            Artisan::command($command, function () use ($command) {
                /** @var \Illuminate\Console\Command $this */
                $this->error(
                    "This '{$command}' command is disabled in this 
                        environment for safety."
                );
            });
        }
    }
}
