<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Gate;
use App\Models\Deal;
use App\Policies\DealPolicy;
use App\Models\Lead;
use App\Policies\LeadPolicy;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, $request) {

            if ($request->expectsJson()) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return null;
            }

            if ($e instanceof HttpExceptionInterface && ! app()->environment('local')) {

                $status = $e->getStatusCode();

                return Inertia::render("errors/{$status}", [
                    'status' => $status
                ])->toResponse($request)->setStatusCode($status);
            }

            return null;
        });
    })

    ->booted(function () {

        /*
        |--------------------------------------------------------------------------
        | Super Admin Bypass
        | 
        | This Gate::before callback will be executed before any other 
        | authorization checks.
        |--------------------------------------------------------------------------
        */
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Permission String Abilities
        | 
        | This Gate::before callback allows us to check for permissions 
        | using simple string abilities like 'deals.view' instead of defining 
        | a method for each permission in the policy. It checks if the user has 
        | the required permission and grants access if they do.
        |--------------------------------------------------------------------------
        */
        Gate::before(function ($user, $ability) {
            if ($user->hasPermission($ability)) {
                return true;
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Register Policies
        |
        | Here we register the DealPolicy for the Deal model. This allows us 
        | to use the policy methods for authorization checks on Deal instances.
        | We also register the LeadPolicy for the Lead model to handle authorization
        | for Lead instances.
        |--------------------------------------------------------------------------
        */
        Gate::policy(Deal::class, DealPolicy::class);
        Gate::policy(Lead::class, LeadPolicy::class);
    })

    ->create();