<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Gate;
use App\Models\Activity;
use App\Policies\ActivityPolicy;
use App\Models\Attachment;
use App\Policies\AttachmentPolicy;
use App\Models\Company;
use App\Policies\CompanyPolicy;
use App\Models\Contact;
use App\Policies\ContactPolicy;
use App\Models\Deal;
use App\Policies\DealPolicy;
use App\Models\Invoice;
use App\Policies\InvoicePolicy;
use App\Models\InvoiceItem;
use App\Policies\InvoiceItemPolicy;
use App\Models\JobTitle;
use App\Policies\JobTitlePolicy;
use App\Models\Lead;
use App\Policies\LeadPolicy;
use App\Models\Learning;
use App\Policies\LearningPolicy;
use App\Models\Note;
use App\Policies\NotePolicy;
use App\Models\Permission;
use App\Policies\PermissionPolicy;
use App\Models\Pipeline;
use App\Policies\PipelinePolicy;
use App\Models\PipelineStage;
use App\Policies\PipelineStagePolicy;
use App\Models\Product;
use App\Policies\ProductPolicy;
use App\Models\Quote;
use App\Policies\QuotePolicy;
use App\Models\Role;
use App\Policies\RolePolicy;
use App\Models\Task;
use App\Policies\TaskPolicy;
use App\Models\User;
use App\Policies\UserPolicy;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withEvents(discover: [
        __DIR__.'/../app/Listeners',
    ])

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

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(
                optional($request->user())->id ?: $request->ip()
            );
        });

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
        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(Attachment::class, AttachmentPolicy::class);
        Gate::policy(Company::class, CompanyPolicy::class);
        Gate::policy(Contact::class, ContactPolicy::class);
        Gate::policy(Deal::class, DealPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(InvoiceItem::class, InvoiceItemPolicy::class);
        Gate::policy(JobTitle::class, JobTitlePolicy::class);
        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(Learning::class, LearningPolicy::class);
        Gate::policy(Note::class, NotePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Pipeline::class, PipelinePolicy::class);
        Gate::policy(PipelineStage::class, PipelineStagePolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Quote::class, QuotePolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    })

    ->create();