<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceItemController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\PipelineStageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Authenticated User
    |--------------------------------------------------------------------------
    */
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */
    Route::apiResource('users', UserController::class)
        ->middleware('can:users.view')
        ->only(['index', 'show']);

    Route::post('users', [UserController::class, 'store'])
        ->middleware('can:users.create');

    Route::put('users/{user}', [UserController::class, 'update'])
        ->middleware('can:users.update');

    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->middleware('can:users.delete');

    Route::post('users/{id}/restore', [UserController::class, 'restore'])
        ->middleware('can:users.update')
        ->name('users.restore');

    Route::delete('users/{id}/force', [UserController::class, 'forceDelete'])
        ->middleware('can:users.delete')
        ->name('users.forceDelete');

    Route::post('users/{user}/roles', [UserController::class, 'attachRoles'])
        ->middleware('can:settings.manage')
        ->name('users.roles.attach');

    Route::delete('users/{user}/roles', [UserController::class, 'detachRoles'])
        ->middleware('can:settings.manage')
        ->name('users.roles.detach');

    /*
    |--------------------------------------------------------------------------
    | Roles & Permissions
    |--------------------------------------------------------------------------
    */
    Route::apiResource('roles', RoleController::class)
        ->middleware('can:settings.manage');

    Route::apiResource('permissions', PermissionController::class)
        ->only(['index', 'show', 'store', 'update', 'destroy'])
        ->middleware('can:settings.manage');

    Route::post(
        'roles/{role}/permissions',
        [RoleController::class, 'syncPermissions']
    )
        ->middleware('can:settings.manage')
        ->name('roles.permissions.sync');

    /*
    |--------------------------------------------------------------------------
    | Companies
    |--------------------------------------------------------------------------
    */
    Route::apiResource('companies', CompanyController::class)
        ->middleware('can:companies.view')
        ->only(['index', 'show']);

    Route::post('companies', [CompanyController::class, 'store'])
        ->middleware('can:companies.create');

    Route::put('companies/{company}', [CompanyController::class, 'update'])
        ->middleware('can:companies.update');

    Route::delete('companies/{company}', [CompanyController::class, 'destroy'])
        ->middleware('can:companies.delete');

    Route::post('companies/{id}/restore', [CompanyController::class, 'restore'])
        ->middleware('can:companies.update')
        ->name('companies.restore');

    Route::delete(
        'companies/{id}/force',
        [CompanyController::class, 'forceDelete']
    )
        ->middleware('can:companies.delete')
        ->name('companies.forceDelete');

    /*
    |--------------------------------------------------------------------------
    | Contacts
    |--------------------------------------------------------------------------
    */
    Route::apiResource('contacts', ContactController::class)
        ->middleware('can:contacts.view')
        ->only(['index', 'show']);

    Route::post('contacts', [ContactController::class, 'store'])
        ->middleware('can:contacts.create');

    Route::put('contacts/{contact}', [ContactController::class, 'update'])
        ->middleware('can:contacts.update');

    Route::delete('contacts/{contact}', [ContactController::class, 'destroy'])
        ->middleware('can:contacts.delete');

    /*
    |--------------------------------------------------------------------------
    | Learnings
    |--------------------------------------------------------------------------
    */
    Route::apiResource('learnings', LearningController::class)
        ->middleware('can:learning.view')
        ->only(['index', 'show']);

    Route::post('learnings', [LearningController::class, 'store'])
        ->middleware('can:learning.create');

    Route::put('learnings/{learning}', [LearningController::class, 'update'])
        ->middleware('can:learning.update');

    Route::delete(
        'learnings/{learning}',
        [LearningController::class, 'destroy']
    )
        ->middleware('can:learning.delete');

    Route::post(
        'learnings/{learning}/complete',
        [LearningController::class, 'complete']
    )
        ->middleware('can:learning.access')
        ->name('learnings.complete');

    Route::post(
        'learnings/{learning}/incomplete',
        [LearningController::class, 'incomplete']
    )
        ->middleware('can:learning.access')
        ->name('learnings.incomplete');

    /*
    |--------------------------------------------------------------------------
    | Pipelines & Stages
    |--------------------------------------------------------------------------
    */
    Route::apiResource('pipelines', PipelineController::class)
        ->middleware('can:pipelines.view')
        ->only(['index', 'show']);

    Route::post('pipelines', [PipelineController::class, 'store'])
        ->middleware('can:pipelines.manage');

    Route::put('pipelines/{pipeline}', [PipelineController::class, 'update'])
        ->middleware('can:pipelines.manage');

    Route::delete(
        'pipelines/{pipeline}',
        [PipelineController::class, 'destroy']
    )
        ->middleware('can:pipelines.manage');

    Route::apiResource('pipeline-stages', PipelineStageController::class)
        ->middleware('can:pipelines.manage');

    /*
    |--------------------------------------------------------------------------
    | Deals (Policy handles update own/any)
    |--------------------------------------------------------------------------
    */
    Route::apiResource('deals', DealController::class)
        ->middleware('can:deals.view')
        ->only(['index', 'show']);

    Route::post('deals', [DealController::class, 'store'])
        ->middleware('can:deals.create');

    Route::put('deals/{deal}', [DealController::class, 'update'])
        ->middleware('can:update,deal');

    Route::delete('deals/{deal}', [DealController::class, 'destroy'])
        ->middleware('can:deals.delete');

    Route::post('deals/{id}/restore', [DealController::class, 'restore'])
        ->middleware('can:deals.update.any')
        ->name('deals.restore');

    Route::delete('deals/{id}/force', [DealController::class, 'forceDelete'])
        ->middleware('can:deals.delete')
        ->name('deals.forceDelete');

    /*
    |--------------------------------------------------------------------------
    | Tasks
    |--------------------------------------------------------------------------
    */
    Route::apiResource('tasks', TaskController::class)
        ->middleware('can:tasks.view')
        ->only(['index', 'show']);

    Route::post('tasks', [TaskController::class, 'store'])
        ->middleware('can:tasks.create');

    Route::put('tasks/{task}', [TaskController::class, 'update'])
        ->middleware('can:tasks.update');

    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])
        ->middleware('can:tasks.delete');

    /*
    |--------------------------------------------------------------------------
    | Notes
    |--------------------------------------------------------------------------
    */
    Route::apiResource('notes', NoteController::class)
        ->middleware('can:notes.create');

    /*
    |--------------------------------------------------------------------------
    | Activities
    |--------------------------------------------------------------------------
    */
    Route::apiResource('activities', ActivityController::class)
        ->middleware('can:reports.view');

    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */
    Route::apiResource('products', ProductController::class)
        ->middleware('can:invoices.view')
        ->only(['index', 'show']);

    Route::post('products', [ProductController::class, 'store'])
        ->middleware('can:invoices.create');

    Route::put('products/{product}', [ProductController::class, 'update'])
        ->middleware('can:invoices.update');

    Route::delete('products/{product}', [ProductController::class, 'destroy'])
        ->middleware('can:invoices.delete');

    /*
    |--------------------------------------------------------------------------
    | Invoices & Items
    |--------------------------------------------------------------------------
    */
    Route::apiResource('invoices', InvoiceController::class)
        ->middleware('can:invoices.view')
        ->only(['index', 'show']);

    Route::post('invoices', [InvoiceController::class, 'store'])
        ->middleware('can:invoices.create');

    Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])
        ->middleware('can:invoices.update');

    Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])
        ->middleware('can:invoices.delete');

    Route::apiResource('invoice-items', InvoiceItemController::class)
        ->middleware('can:invoices.update');

    /*
    |--------------------------------------------------------------------------
    | Attachments
    |--------------------------------------------------------------------------
    */
    Route::apiResource('attachments', AttachmentController::class)
        ->except(['update'])
        ->middleware('can:attachments.upload');
});
