<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Deal;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\Part;
use App\Models\Product;
use App\Models\Task;
use App\Models\User;
use App\Observers\InvoiceItemObserver;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

/**
 * Bootstraps core application services and framework integrations.
 *
 * This provider configures global model behaviour, including polymorphic
 * relation mappings and billing integration via Laravel Cashier.
 * It also registers environment-specific safeguards and event listeners
 * that respond to external service events.
 *
 * In non-local environments, destructive Artisan commands are disabled
 * to prevent accidental data loss.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * This method is reserved for binding services into the container.
     *
     * @return void
     */
    public function register(): void
    {
        // Empty register method
    }

    /**
     * Bootstrap any application services.
     *
     * Configures morph maps, billing behaviour, environment safeguards,
     * and event listeners.
     *
     * @return void
     */
    public function boot(): void
    {
        Route::bind('part', function ($value) {
            return Part::findOrFail($value);
        });

        Route::bind('product', function ($value) {
            return Product::findOrFail($value);
        });

        Relation::morphMap([
            'company' => Company::class,
            'deal' => Deal::class,
            'task' => Task::class,
            'part' => Part::class,
            'product' => Product::class,
            'user' => User::class,
        ]);

        InvoiceItem::observe(InvoiceItemObserver::class);

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
     * Prevent destructive Artisan commands from running in protected
     * environments.
     *
     * Overrides selected commands with safe handlers that output an error
     * message instead of executing potentially destructive operations such
     * as dropping tables or resetting the database.
     *
     * @return void
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
