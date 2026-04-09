<?php

use App\Models\Activity;
use App\Models\Attachment;
use App\Models\Company;
use App\Models\Deal;
use App\Models\Industry;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JobTitle;
use App\Models\Lead;
use App\Models\Learning;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Services\Activities\ActivityQueryService;
use App\Services\Attachments\AttachmentQueryService;
use App\Services\Companies\CompanyQueryService;
use App\Services\Deals\DealQueryService;
use App\Services\Industries\IndustryQueryService;
use App\Services\InvoiceItems\InvoiceItemQueryService;
use App\Services\Invoices\InvoiceQueryService;
use App\Services\JobTitles\JobTitleQueryService;
use App\Services\Leads\LeadQueryService;
use App\Services\Learnings\LearningQueryService;
use App\Services\Users\UserQueryService;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    /**
     * -------------------------------
     * ------------ Users ------------
     * -------------------------------
     */
    Route::get('/users', function (
        Request $request,
        UserQueryService $service
    ) {
        return Inertia::render('Users/Index', [
            'users' => $service->list($request),
        ]);
    })->name('users.index');

    Route::get('/users/create', function () {
        return Inertia::render('Users/Create', [
            'roles' => Role::all(),
            'jobTitles' => JobTitle::all(),
        ]);
    })->name('users.create');

    Route::get('/users/{user}', function (
        User $user,
        UserQueryService $service
    ) {
        return Inertia::render('Users/Show', [
            'user' => $service->show($user),
        ]);
    })->name('users.show');

    Route::get('/users/{user}/edit', function (User $user) {
        return Inertia::render('Users/Update', [
            'user' => $user->load('role', 'jobTitle'),
            'roles' => Role::all(),
            'jobTitles' => JobTitle::all(),
        ]);
    })->name('users.edit');

    /**
     * ------------------------------------
     * ------------ Activities ------------
     * ------------------------------------
     */
    Route::get('/activities', function (
        Request $request,
        ActivityQueryService $service
    ) {
        return Inertia::render('Activities/Index', [
            'activities' => $service->list($request),
        ]);
    })->name('activities.index');

    Route::get('/activities/create', function () {
        return Inertia::render('Activities/Create', [
            'subjectTypes' => array_keys(Relation::morphMap()),
        ]);
    })->name('activities.create');

    Route::get('/activities/{activity}', function (
        Activity $activity,
        ActivityQueryService $service
    ) {
        return Inertia::render('Activities/Show', [
            'activity' => $service->show($activity),
        ]);
    })->name('activities.show');

    Route::get('/activities/{activity}/edit', function (Activity $activity) {
        return Inertia::render('Activities/Update', [
            'activity' => $activity->load([
                'user',
                'subject',
            ]),
            'subjectTypes' => array_keys(Relation::morphMap()),
        ]);
    })->name('activities.edit');

    /**
     * -------------------------------------
     * ------------ Attachments ------------
     * -------------------------------------
     */
    Route::get('/attachments', function (
        Request $request,
        AttachmentQueryService $service
    ) {
        return Inertia::render('Attachments/Index', [
            'attachments' => $service->list($request),
        ]);
    })->name('attachments.index');

    Route::get('/attachments/create', function () {
        return Inertia::render('Attachments/Create', [
            'attachableTypes' => Attachment::ATTACHABLE_TYPES,
        ]);
    })->name('attachments.create');

    Route::get('/attachments/{attachment}', function (
        Attachment $attachment,
        AttachmentQueryService $service
    ) {
        return Inertia::render('Attachments/Show', [
            'attachment' => $service->show($attachment),
        ]);
    })->name('attachments.show');

    Route::get(
        '/attachments/{attachment}/edit',
        function (Attachment $attachment) {
            return Inertia::render('Attachments/Update', [
                'attachment' => $attachment->load(['uploader']),
                'attachableTypes' => Attachment::ATTACHABLE_TYPES,
            ]);
        }
    )->name('attachments.edit');

    Route::get(
        '/attachments/{attachment}/download',
        function (Attachment $attachment) {
            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk($attachment->disk);

            return $disk->response($attachment->path, $attachment->filename);
        }
    )->name('attachments.download');

    /**
     * -------------------------------------
     * ------------- Companies -------------
     * -------------------------------------
     */
    Route::get('/companies', function (
        Request $request,
        CompanyQueryService $service
    ) {
        return Inertia::render('Companies/Index', [
            'companies' => $service->list($request),
        ]);
    })->name('companies.index');

    Route::get('/companies/create', function () {
        return Inertia::render('Companies/Create', [
            'industries' => Industry::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('companies.create');

    Route::get('/companies/{company}', function (
        Company $company,
        CompanyQueryService $service
    ) {
        return Inertia::render('Companies/Show', [
            'company' => $service->show($company),
        ]);
    })->name('companies.show');

    Route::get('/companies/{company}/edit', function (Company $company) {
        return Inertia::render('Companies/Update', [
            'company' => $company->load([
                'deals',
                'industries',
                'invoices',
                'attachments',
            ]),
            'industries' => Industry::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('companies.edit');

    /**
     * --------------------------------------
     * ------------- Industries -------------
     * --------------------------------------
     */
    Route::get('/industries', function (
        Request $request,
        IndustryQueryService $service
    ) {
        return Inertia::render('Industries/Index', [
            'industries' => $service->list($request),
        ]);
    })->name('industries.index');

    Route::get('/industries/create', function () {
        return Inertia::render('Industries/Create');
    })->name('industries.create');

    Route::get('/industries/{industry}', function (
        Industry $industry,
        IndustryQueryService $service
    ) {
        return Inertia::render('Industries/Show', [
            'industry' => $service->show($industry),
        ]);
    })->name('industries.show');

    Route::get('/industries/{industry}/edit', function (Industry $industry) {
        return Inertia::render('Industries/Update', [
            'industry' => $industry->load([
                'companies',
            ]),
        ]);
    })->name('industries.edit');

    /**
     * -------------------------------------
     * --------------- Deals ---------------
     * -------------------------------------
     */
    Route::get('/deals', function (
        Request $request,
        DealQueryService $service
    ) {
        return Inertia::render('Deals/Index', [
            'deals' => $service->list($request),
        ]);
    })->name('deals.index');

    Route::get('/deals/create', function () {
        return Inertia::render('Deals/Create', [
            'companies' => Company::orderBy('name')->get(['id', 'name']),
            'owners' => User::orderBy('name')->get(['id', 'name']),
            'pipelines' => Pipeline::orderBy('name')->get(['id', 'name']),
            'stages' => PipelineStage::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('deals.create');

    Route::get('/deals/{deal}', function (
        Deal $deal,
        DealQueryService $service
    ) {
        return Inertia::render('Deals/Show', [
            'deal' => $service->show($deal),
        ]);
    })->name('deals.show');

    Route::get('/deals/{deal}/edit', function (Deal $deal) {
        return Inertia::render('Deals/Update', [
            'deal' => $deal->load([
                'company',
                'owner',
                'pipeline',
                'stage',
                'products',
            ]),
            'companies' => Company::orderBy('name')->get(['id', 'name']),
            'owners' => User::orderBy('name')->get(['id', 'name']),
            'pipelines' => Pipeline::orderBy('name')->get(['id', 'name']),
            'stages' => PipelineStage::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('deals.edit');

    /**
     * -----------------------------------------
     * ------------- Deal Products -------------
     * -----------------------------------------
     */
    Route::get('/deals/{deal}/products', function (Deal $deal) {
        return Inertia::render('DealProducts/Index', [
            'deal' => $deal->load([
                'products',
            ]),
        ]);
    })->name('deals.products.index');

    Route::get('/deals/{deal}/products/add', function (Deal $deal) {
        return Inertia::render('DealProducts/Add', [
            'deal' => $deal,
            'products' => Product::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('deals.products.add');

    Route::get(
        '/deals/{deal}/products/{product}/edit',
        function (Deal $deal, Product $product) {
            return Inertia::render('DealProducts/Edit', [
                'deal' => $deal,
                'product' => $product,
            ]);
        }
    )->name('deals.products.edit');

    /**
     * -------------------------------------
     * ------------- Invoices -------------
     * -------------------------------------
     */
    Route::get('/invoices', function (
        Request $request,
        InvoiceQueryService $service
    ) {
        return Inertia::render('Invoices/Index', [
            'invoices' => $service->list($request),
        ]);
    })->name('invoices.index');

    Route::get('/invoices/create', function () {
        return Inertia::render('Invoices/Create', [
            'companies' => Company::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('invoices.create');

    Route::get('/invoices/{invoice}', function (
        Invoice $invoice,
        InvoiceQueryService $service
    ) {
        return Inertia::render('Invoices/Show', [
            'invoice' => $service->show($invoice),
        ]);
    })->name('invoices.show');

    Route::get('/invoices/{invoice}/edit', function (Invoice $invoice) {
        return Inertia::render('Invoices/Update', [
            'invoice' => $invoice->load([
                'company',
                'items',
            ]),
            'companies' => Company::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('invoices.edit');

    /**
     * -------------------------------------
     * ---------- Invoice Items ------------
     * -------------------------------------
     */
    Route::get('/invoice-items', function (
        Request $request,
        InvoiceItemQueryService $service
    ) {
        return Inertia::render('InvoiceItems/Index', [
            'invoiceItems' => $service->list($request),
        ]);
    })->name('invoice-items.index');

    Route::get('/invoice-items/create', function () {
        return Inertia::render('InvoiceItems/Create', [
            'invoices' => Invoice::orderBy('id')->get(['id']),
            'products' => Product::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('invoice-items.create');

    Route::get('/invoice-items/{invoiceItem}', function (
        InvoiceItem $invoiceItem,
        InvoiceItemQueryService $service
    ) {
        return Inertia::render('InvoiceItems/Show', [
            'invoiceItem' => $service->show($invoiceItem),
        ]);
    })->name('invoice-items.show');

    Route::get('/invoice-items/{invoiceItem}/edit', function (
        InvoiceItem $invoiceItem
    ) {
        return Inertia::render('InvoiceItems/Update', [
            'invoiceItem' => $invoiceItem->load([
                'invoice',
                'product',
            ]),
            'invoices' => Invoice::orderBy('id')->get(['id']),
            'products' => Product::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('invoice-items.edit');

    /**
     * -------------------------------------
     * ----------- Job Titles --------------
     * -------------------------------------
     */
    Route::get('/job-titles', function (
        Request $request,
        JobTitleQueryService $service
    ) {
        return Inertia::render('JobTitles/Index', [
            'jobTitles' => $service->list($request),
        ]);
    })->name('job-titles.index');

    Route::get('/job-titles/create', function () {
        return Inertia::render('JobTitles/Create');
    })->name('job-titles.create');

    Route::get('/job-titles/{jobTitle}', function (
        JobTitle $jobTitle,
        JobTitleQueryService $service
    ) {
        return Inertia::render('JobTitles/Show', [
            'jobTitle' => $service->show($jobTitle),
        ]);
    })->name('job-titles.show');

    Route::get('/job-titles/{jobTitle}/edit', function (
        JobTitle $jobTitle
    ) {
        return Inertia::render('JobTitles/Update', [
            'jobTitle' => $jobTitle->load([
                'users',
                'creator',
                'updater',
            ]),
        ]);
    })->name('job-titles.edit');

    /**
     * -------------------------------------
     * --------------- Leads ---------------
     * -------------------------------------
     */
    Route::get('/leads', function (
        Request $request,
        LeadQueryService $service
    ) {
        return Inertia::render('Leads/Index', [
            'leads' => $service->list($request),
        ]);
    })->name('leads.index');

    Route::get('/leads/create', function () {
        return Inertia::render('Leads/Create', [
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('leads.create');

    Route::get('/leads/{lead}', function (
        Lead $lead,
        LeadQueryService $service
    ) {
        return Inertia::render('Leads/Show', [
            'lead' => $service->show($lead),
        ]);
    })->name('leads.show');

    Route::get('/leads/{lead}/edit', function (
        Lead $lead
    ) {
        return Inertia::render('Leads/Update', [
            'lead' => $lead->load([
                'owner',
                'assignedTo',
                'creator',
                'updater',
                'deleter',
            ]),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('leads.edit');

    /**
     * -------------------------------------
     * ------------- Learnings -------------
     * -------------------------------------
     */
    Route::get('/learnings', function (
        Request $request,
        LearningQueryService $service
    ) {
        return Inertia::render('Learnings/Index', [
            'learnings' => $service->list($request),
        ]);
    })->name('learnings.index');

    Route::get('/learnings/create', function () {
        return Inertia::render('Learnings/Create', [
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('learnings.create');

    Route::get('/learnings/{learning}', function (
        Learning $learning,
        LearningQueryService $service
    ) {
        return Inertia::render('Learnings/Show', [
            'learning' => $service->show($learning),
        ]);
    })->name('learnings.show');

    Route::get('/learnings/{learning}/edit', function (
        Learning $learning
    ) {
        return Inertia::render('Learnings/Update', [
            'learning' => app(LearningQueryService::class)->show($learning),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('learnings.edit');

    /**
     * -------------------------------------
     * -------- Learning Completion --------
     * -------------------------------------
     */

    // Page to take/complete a learning (quiz / assessment)
    Route::get('/learnings/{learning}/complete', function (
        Learning $learning,
        LearningQueryService $service
    ) {
        return Inertia::render('Learnings/Complete', [
            'learning' => $service->show($learning),
        ]);
    })->name('learnings.complete');

    // Results page after completion
    Route::get('/learnings/{learning}/results', function (
        Learning $learning,
        LearningQueryService $service
    ) {
        return Inertia::render('Learnings/Results', [
            'learning' => $service->show($learning),
        ]);
    })->name('learnings.results');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
