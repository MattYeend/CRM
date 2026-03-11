<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceItemController;
use App\Http\Controllers\LeadController;
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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // Users + role pivot helpers
    Route::apiResource('users', UserController::class);
    Route::post(
        'users/{id}/restore',
        [UserController::class, 'restore']
    )->name('users.restore');
    Route::delete(
        'users/{id}/force',
        [UserController::class, 'forceDelete']
    )->name('users.forceDelete');
    Route::post(
        'users/{user}/roles',
        [UserController::class, 'attachRoles']
    )->name('users.roles.attach');
    Route::delete(
        'users/{user}/roles',
        [UserController::class, 'detachRoles']
    )->name('users.roles.detach');

    // Roles & permissions (role <-> permission handled in RoleController)
    Route::apiResource('roles', RoleController::class)->only(['index', 'show']);
    Route::apiResource('permissions', PermissionController::class);
    Route::post(
        'permissions/{id}/restore',
        [PermissionController::class, 'restore']
    )->name('permissions.restore');

    // Companies & Contacts
    Route::apiResource('companies', CompanyController::class);
    Route::post(
        'companies/{id}/restore',
        [CompanyController::class, 'restore']
    )->name('companies.restore');

    Route::apiResource('contacts', ContactController::class);
    Route::post(
        'contacts/{id}/restore',
        [ContactController::class, 'restore']
    )->name('contacts.restore');

    // Learnings
    Route::apiResource('learnings', LearningController::class);
    Route::post(
        'learnings/{learning}/complete',
        [LearningController::class, 'complete']
    )->name('learnings.complete');
    Route::post(
        'learnings/{learning}/incomplete',
        [LearningController::class, 'incomplete']
    )->name('learnings.incomplete');
    Route::post(
        'learnings/{id}/restore',
        [LearningController::class, 'restore']
    )->name('learnings.restore');

    // Pipelines & stages
    Route::apiResource('pipelines', PipelineController::class);
    Route::post(
        'pipelines/{id}/restore',
        [PipelineController::class, 'restore']
    )->name('pipelines.restore');
    Route::apiResource('pipeline-stages', PipelineStageController::class);
    Route::post(
        'pipeline-stages/{id}/restore',
        [PipelineStageController::class, 'restore']
    )->name('pipelineStages.restore');

    // Deals
    Route::apiResource('deals', DealController::class);
    Route::post(
        'deals/{id}/restore',
        [DealController::class, 'restore']
    )->name('deals.restore');
    Route::delete(
        'deals/{id}/force',
        [DealController::class, 'forceDelete']
    )->name('deals.forceDelete');

    // Tasks, Notes, Activities
    Route::apiResource('tasks', TaskController::class);
    Route::post(
        'tasks/{id}/restore',
        [TaskController::class, 'restore']
    )->name('tasks.restore');
    Route::apiResource('notes', NoteController::class);
    Route::post(
        'notes/{id}/restore',
        [NoteController::class, 'restore']
    )->name('notes.restore');
    Route::apiResource('activities', ActivityController::class);
    Route::post(
        'activities/{id}/restore',
        [ActivityController::class, 'restore']
    )->name('activities.restore');

    // Products, Invoices, Invoice Items
    Route::apiResource('products', ProductController::class);
    Route::post(
        'products/{id}/restore',
        [ProductController::class, 'restore']
    )->name('products.restore');
    Route::apiResource('invoices', InvoiceController::class);
    Route::post(
        'invoices/{id}/restore',
        [InvoiceController::class, 'restore']
    )->name('invoices.restore');
    Route::apiResource('invoice-items', InvoiceItemController::class);
    Route::post(
        'invoice-items/{id}/restore',
        [InvoiceItemController::class, 'restore']
    )->name('invoice-items.restore');

    Route::apiResource('attachments', AttachmentController::class);
    Route::post(
        'attachments/{id}/restore',
        [AttachmentController::class, 'restore']
    )->name('attachments.restore');

    // Role permission sync (if you want a dedicated endpoint)
    Route::post(
        'roles/{role}/permissions',
        [RoleController::class, 'syncPermissions']
    )->name('roles.permissions.sync');

    // Force delete/restore endpoints for other soft-deletable resources
    Route::delete(
        'companies/{id}/force',
        [CompanyController::class, 'forceDelete']
    )->name('companies.forceDelete');

    Route::apiResource('leads', LeadController::class);
    Route::post(
        'leads/{id}/restore',
        [LeadController::class, 'restore']
    )->name('leads.restore');
    Route::delete(
        'leads/{id}/force',
        [LeadController::class, 'forceDelete']
    )->name('leads.forceDelete');
});
