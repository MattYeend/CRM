<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceItemController;
use App\Http\Controllers\JobTitleController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\OrderCheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\PipelineStageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['web','auth:sanctum']);

Route::middleware(['web', 'auth:sanctum', 'throttle:api'])->group(function () {
    /**
     * ----------------------------------------------------------
     * -------------- Users, Roles, & Permissions ---------------
     * ----------------------------------------------------------
     */
    Route::apiResource('users', UserController::class)
        ->names([
            'index' => 'api.users.index',
            'store' => 'api.users.store',
            'show' => 'api.users.show',
            'update' => 'api.users.update',
            'destroy' => 'api.users.destroy',
        ]);
    Route::post(
        'users/{id}/restore',
        [UserController::class, 'restore']
    )->name('api.users.restore');

    Route::apiResource('roles', RoleController::class)
        ->only(['index', 'show'])
        ->names([
            'index' => 'api.roles.index',
            'show' => 'api.roles.show',
        ]);
    Route::post(
        'roles/{role}/permissions',
        [RoleController::class, 'syncPermissions']
    )->name('api.roles.permissions.sync');

    Route::apiResource('permissions', PermissionController::class)
        ->names([
            'index' => 'api.permissions.index',
            'store' => 'api.permissions.store',
            'show' => 'api.permissions.show',
            'update' => 'api.permissions.update',
            'destroy' => 'api.permissions.destroy',
        ]);
    Route::post(
        'permissions/{id}/restore',
        [PermissionController::class, 'restore']
    )->name('api.permissions.restore');

    /**
     * ----------------------------------------------------------
     * ------------------ Compnies & Contacts -------------------
     * ----------------------------------------------------------
     */
    Route::apiResource('companies', CompanyController::class)->names([
        'index' => 'api.companies.index',
        'store' => 'api.companies.store',
        'show' => 'api.companies.show',
        'update' => 'api.companies.update',
        'destroy' => 'api.companies.destroy',
    ]);
    Route::post(
        'companies/{id}/restore',
        [CompanyController::class, 'restore']
    )->name('api.companies.restore');
    Route::delete(
        'companies/{id}/force',
        [CompanyController::class, 'forceDelete']
    )->name('api.companies.forceDelete');

    Route::apiResource('contacts', ContactController::class)->names([
        'index' => 'api.contacts.index',
        'store' => 'api.contacts.store',
        'show' => 'api.contacts.show',
        'update' => 'api.contacts.update',
        'destroy' => 'api.contacts.destroy',
    ]);
    Route::post(
        'contacts/{id}/restore',
        [ContactController::class, 'restore']
    )->name('api.contacts.restore');

    /**
     * ----------------------------------------------------------
     * ----------------------- Learnings ------------------------
     * ----------------------------------------------------------
     */
    Route::apiResource('learnings', LearningController::class)
        ->names([
            'index' => 'api.learnings.index',
            'store' => 'api.learnings.store',
            'show' => 'api.learnings.show',
            'update' => 'api.learnings.update',
            'destroy' => 'api.learnings.destroy',
        ]);
    Route::post(
        'learnings/{learning}/complete',
        [LearningController::class, 'complete']
    )->name('api.learnings.complete');
    Route::post(
        'learnings/{learning}/incomplete',
        [LearningController::class, 'incomplete']
    )->name('api.learnings.incomplete');
    Route::post(
        'learnings/{id}/restore',
        [LearningController::class, 'restore']
    )->name('api.learnings.restore');

    /**
     * -----------------------------------------------------------
     * ------------------- Pipelines & Stages --------------------
     * -----------------------------------------------------------
     */
    Route::apiResource('pipelines', PipelineController::class)
        ->names([
            'index' => 'api.pipelines.index',
            'store' => 'api.pipelines.store',
            'show' => 'api.pipelines.show',
            'update' => 'api.pipelines.update',
            'destroy' => 'api.pipelines.destroy',
        ]);
    Route::post(
        'pipelines/{id}/restore',
        [PipelineController::class, 'restore']
    )->name('api.pipelines.restore');

    Route::apiResource('pipeline-stages', PipelineStageController::class)
        ->names([
            'index' => 'api.pipelineStages.index',
            'store' => 'api.pipelineStages.store',
            'show' => 'api.pipelineStages.show',
            'update' => 'api.pipelineStages.update',
            'destroy' => 'api.pipelineStages.destroy',
        ]);
    Route::post(
        'pipeline-stages/{id}/restore',
        [PipelineStageController::class, 'restore']
    )->name('api.pipelineStages.restore');

    /**
     * ---------------------------------------------------------
     * ------------------------- Deals -------------------------
     * ---------------------------------------------------------
     */
    Route::apiResource('deals', DealController::class)->names([
        'index' => 'api.deals.index',
        'store' => 'api.deals.store',
        'show' => 'api.deals.show',
        'update' => 'api.deals.update',
        'destroy' => 'api.deals.destroy',
    ]);
    Route::post(
        'deals/{id}/restore',
        [DealController::class, 'restore']
    )->name('api.deals.restore');
    Route::delete(
        'deals/{id}/force',
        [DealController::class, 'forceDelete']
    )->name('api.deals.forceDelete');

    /**
     * ----------------------------------------------------------
     * --------------- Tasks, Notes, & Activities ---------------
     * ----------------------------------------------------------
     */
    Route::apiResource('tasks', TaskController::class)->names([
        'index' => 'api.tasks.index',
        'store' => 'api.tasks.store',
        'show' => 'api.tasks.show',
        'update' => 'api.tasks.update',
        'destroy' => 'api.tasks.destroy',
    ]);
    Route::post(
        'tasks/{id}/restore',
        [TaskController::class, 'restore']
    )->name('api.tasks.restore');

    Route::apiResource('notes', NoteController::class)
        ->names([
            'index' => 'api.notes.index',
            'store' => 'api.notes.store',
            'show' => 'api.notes.show',
            'update' => 'api.notes.update',
            'destroy' => 'api.notes.destroy',
        ]);
    Route::post(
        'notes/{id}/restore',
        [NoteController::class, 'restore']
    )->name('api.notes.restore');

    Route::apiResource('activities', ActivityController::class)->names([
        'index' => 'api.activities.index',
        'store' => 'api.activities.store',
        'show' => 'api.activities.show',
        'update' => 'api.activities.update',
        'destroy' => 'api.activities.destroy',
    ]);
    Route::post(
        'activities/{id}/restore',
        [ActivityController::class, 'restore']
    )->name('api.activities.restore');

    /**
     * ----------------------------------------------------------
     * ------------------------ Products ------------------------
     * ----------------------------------------------------------
     */
    Route::apiResource('products', ProductController::class)
        ->names([
            'index' => 'api.products.index',
            'store' => 'api.products.store',
            'show' => 'api.products.show',
            'update' => 'api.products.update',
            'destroy' => 'api.products.destroy',
        ]);
    Route::post(
        'products/{id}/restore',
        [ProductController::class, 'restore']
    )->name('api.products.restore');

    Route::post(
        'products/{product}/orders',
        [ProductController::class, 'addOrders']
    )->name('api.products.orders.add');

    Route::put(
        'products/{product}/orders',
        [ProductController::class, 'updateOrders']
    )->name('api.products.orders.update');

    Route::delete(
        'products/{product}/orders/{order}',
        [ProductController::class, 'removeOrder']
    )->name('api.products.orders.remove');

    Route::post(
        'products/{product}/orders/{order}/restore',
        [ProductController::class, 'restoreOrder']
    )->name('api.products.orders.restore');

    Route::post(
        'products/{product}/quotes',
        [ProductController::class, 'addQuotes']
    )->name('api.products.quotes.add');

    Route::put(
        'products/{product}/quotes',
        [ProductController::class, 'updateQuotes']
    )->name('api.products.quotes.update');

    Route::delete(
        'products/{product}/quotes/{quote}',
        [ProductController::class, 'removeQuote']
    )->name('api.products.quotes.remove');

    Route::post(
        'products/{product}/quotes/{quote}/restore',
        [ProductController::class, 'restoreQuote']
    )->name('api.products.quotes.restore');

    Route::post(
        'products/{product}/deals',
        [ProductController::class, 'addDeals']
    )->name('api.products.deals.add');

    Route::put(
        'products/{product}/deals',
        [ProductController::class, 'updateDeals']
    )->name('api.products.deals.update');

    Route::delete(
        'products/{product}/deals/{deal}',
        [ProductController::class, 'removeDeal']
    )->name('api.products.deals.remove');

    Route::post(
        'products/{product}/deals/{deal}/restore',
        [ProductController::class, 'restoreDeal']
    )->name('api.products.deals.restore');

    /**
     * --------------------------------------------------------
     * ----------------------- Invoices -----------------------
     * --------------------------------------------------------
     */
    Route::apiResource('invoices', InvoiceController::class)
        ->names([
            'index' => 'api.invoices.index',
            'store' => 'api.invoices.store',
            'show' => 'api.invoices.show',
            'update' => 'api.invoices.update',
            'destroy' => 'api.invoices.destroy',
        ]);
    Route::post(
        'invoices/{id}/restore',
        [InvoiceController::class, 'restore']
    )->name('api.invoices.restore');

    Route::apiResource('invoice-items', InvoiceItemController::class)
        ->names([
            'index' => 'api.invoiceItems.index',
            'store' => 'api.invoiceItems.store',
            'show' => 'api.invoiceItems.show',
            'update' => 'api.invoiceItems.update',
            'destroy' => 'api.invoiceItems.destroy',
        ]);
    Route::post(
        'invoice-items/{id}/restore',
        [InvoiceItemController::class, 'restore']
    )->name('api.invoiceItems.restore');

    /**
     * --------------------------------------------------------
     * ------------------------ Quotes ------------------------
     * --------------------------------------------------------
     */
    Route::apiResource('quotes', QuoteController::class);
    Route::post(
        'quotes/{id}/restore',
        [QuoteController::class, 'restore']
    )->name('quotes.restore');

    /**
     * ---------------------------------------------------------
     * ---------------------- Attachments ----------------------
     * ---------------------------------------------------------
     */
    Route::apiResource('attachments', AttachmentController::class);
    Route::post(
        'attachments/{id}/restore',
        [AttachmentController::class, 'restore']
    )->name('attachments.restore');

    /**
     * ---------------------------------------------------------
     * ------------------------- Leads -------------------------
     * ---------------------------------------------------------
     */
    Route::apiResource('leads', LeadController::class);
    Route::post(
        'leads/{id}/restore',
        [LeadController::class, 'restore']
    )->name('leads.restore');
    Route::delete(
        'leads/{id}/force',
        [LeadController::class, 'forceDelete']
    )->name('leads.forceDelete');

    /**
     * ----------------------------------------------------------
     * ------------------------- Orders -------------------------
     * ----------------------------------------------------------
     */
    Route::apiResource('orders', OrderController::class);
    Route::post(
        'orders/{id}/restore',
        [OrderController::class, 'restore']
    )->name('orders.restore');

    /**
     * ----------------------------------------------------------
     * -------------------- Deal Products ----------------------
     * ----------------------------------------------------------
     */
    Route::post(
        'deals/{deal}/products',
        [DealController::class, 'addProducts']
    )->name('deals.products.add');

    Route::put(
        'deals/{deal}/products',
        [DealController::class, 'updateProducts']
    )->name('deals.products.update');

    Route::delete(
        'deals/{deal}/products/{product}',
        [DealController::class, 'removeProduct']
    )->name('deals.products.remove');

    Route::post(
        'deals/{deal}/products/{product}/restore',
        [DealController::class, 'restoreProduct']
    )->name('deals.products.restore');

    /**
     * ----------------------------------------------------------
     * -------------------- Order Products ----------------------
     * ----------------------------------------------------------
     */
    Route::post(
        'orders/{order}/products',
        [OrderController::class, 'addProducts']
    )->name('orders.products.add');

    Route::put(
        'orders/{order}/products',
        [OrderController::class, 'updateProducts']
    )->name('orders.products.update');

    Route::delete(
        'orders/{order}/products/{product}',
        [OrderController::class, 'removeProduct']
    )->name('orders.products.remove');

    Route::post(
        'orders/{order}/products/{product}/restore',
        [OrderController::class, 'restoreProduct']
    )->name('orders.products.restore');

    /**
     * ----------------------------------------------------------
     * -------------------- Quote Products ----------------------
     * ----------------------------------------------------------
     */
    Route::post(
        'quotes/{quote}/products',
        [QuoteController::class, 'addProducts']
    )->name('quotes.products.add');

    Route::put(
        'quotes/{quote}/products',
        [QuoteController::class, 'updateProducts']
    )->name('quotes.products.update');

    Route::delete(
        'quotes/{quote}/products/{product}',
        [QuoteController::class, 'removeProduct']
    )->name('quotes.products.remove');

    Route::post(
        'quotes/{quote}/products/{product}/restore',
        [QuoteController::class, 'restoreProduct']
    )->name('quotes.products.restore');

    /**
     * ----------------------------------------------------------
     * ---------------------- Job Titles ------------------------
     * ----------------------------------------------------------
     */
    Route::apiResource('job-titles', JobTitleController::class);
    Route::post(
        'job-titles/{id}/restore',
        [JobTitleController::class, 'restore']
    )->name('job-titles.restore');

    /**
     * ----------------------------------------------------------
     * -------------------- Order Checkout ----------------------
     * ----------------------------------------------------------
     */
    Route::get(
        '/orders/{order}/checkout',
        [OrderCheckoutController::class, 'checkout']
    )->name('orders.checkout');
});
