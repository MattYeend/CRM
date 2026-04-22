<?php

use App\Models\Activity;
use App\Models\Attachment;
use App\Models\BillOfMaterial;
use App\Models\Company;
use App\Models\Deal;
use App\Models\Industry;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JobTitle;
use App\Models\Lead;
use App\Models\Learning;
use App\Models\Note;
use App\Models\Order;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\PartImage;
use App\Models\PartSerialNumber;
use App\Models\PartStockMovement;
use App\Models\Permission;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\Task;
use App\Models\User;
use App\Services\Activities\ActivityQueryService;
use App\Services\Attachments\AttachmentQueryService;
use App\Services\BillOfMaterials\BillOfMaterialQueryService;
use App\Services\Companies\CompanyQueryService;
use App\Services\Deals\DealQueryService;
use App\Services\Industries\IndustryQueryService;
use App\Services\InvoiceItems\InvoiceItemQueryService;
use App\Services\Invoices\InvoiceQueryService;
use App\Services\JobTitles\JobTitleQueryService;
use App\Services\Leads\LeadQueryService;
use App\Services\Learnings\LearningQueryService;
use App\Services\Notes\NoteQueryService;
use App\Services\Orders\OrderQueryService;
use App\Services\PartCategories\PartCategoryQueryService;
use App\Services\PartImages\PartImageQueryService;
use App\Services\Parts\PartQueryService;
use App\Services\PartSerialNumbers\PartSerialNumberQueryService;
use App\Services\PartStockMovements\PartStockMovementQueryService;
use App\Services\Permissions\PermissionQueryService;
use App\Services\Pipelines\PipelineQueryService;
use App\Services\PipelineStages\PipelineStageQueryService;
use App\Services\Products\ProductQueryService;
use App\Services\Quotes\QuoteQueryService;
use App\Services\Roles\RoleQueryService;
use App\Services\Suppliers\SupplierQueryService;
use App\Services\Tasks\TaskQueryService;
use App\Services\Users\UserQueryService;
use App\Support\ActivityRegistry;
use App\Support\AttachableRegistry;
use App\Support\NotableRegistry;
use App\Support\TaskableRegistry;
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
     * -----------------------------------
     * -------------- Users --------------
     * -----------------------------------
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
     * ----------------------------------------
     * -------------- Activities --------------
     * ----------------------------------------
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
            'subjectTypes' => ActivityRegistry::keys(),
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
            'subjectTypes' => ActivityRegistry::keys(),
        ]);
    })->name('activities.edit');

    /**
     * -----------------------------------------
     * -------------- Attachments --------------
     * -----------------------------------------
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
            'attachableTypes' => AttachableRegistry::keys(),
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
                'attachment' => $attachment->load([
                    'uploader',
                ]),
                'attachableTypes' => AttachableRegistry::keys(),
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
     * -----------------------------------------
     * --------------- Companies ---------------
     * -----------------------------------------
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
     * ------------------------------------------
     * --------------- Industries ---------------
     * ------------------------------------------
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
     * -----------------------------------------
     * ----------------- Deals -----------------
     * -----------------------------------------
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
     * ---------------------------------------------------------
     * ------------- Deal Products (nested: deals) -------------
     * ---------------------------------------------------------
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
            $product = $deal->products()
                ->where('products.id', $product->id)
                ->firstOrFail();
            return Inertia::render('DealProducts/Edit', [
                'deal' => $deal,
                'product' => $product,
            ]);
        }
    )->name('deals.products.edit');

    /**
     * ------------------------------------------
     * ---------------- Invoices ----------------
     * ------------------------------------------
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
     * -----------------------------------------
     * ------------ Invoice Items --------------
     * -----------------------------------------
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
     * -----------------------------------------
     * ------------- Job Titles ----------------
     * -----------------------------------------
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
     * -----------------------------------------
     * ----------------- Leads -----------------
     * -----------------------------------------
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
     * -----------------------------------------
     * --------------- Learnings ---------------
     * -----------------------------------------
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
            'learning' => $learning->load([
                'creator',
                'users',
                'questions.answers',
            ]),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('learnings.edit');

    /**
     * -----------------------------------------
     * ---------- Learning Completion ----------
     * -----------------------------------------
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

    /**
     * -----------------------------------
     * -------------- Notes --------------
     * -----------------------------------
     */
    Route::get('/notes', function (
        Request $request,
        NoteQueryService $service
    ) {
        return Inertia::render('Notes/Index', [
            'notes' => $service->list($request),
        ]);
    })->name('notes.index');

    Route::get('/notes/create', function () {
        return Inertia::render('Notes/Create', [
            'notableTypes' => NotableRegistry::keys(),
        ]);
    })->name('notes.create');

    Route::get('/notes/{note}', function (
        Note $note,
        NoteQueryService $service
    ) {
        return Inertia::render('Notes/Show', [
            'note' => $service->show($note),
        ]);
    })->name('notes.show');

    Route::get('/notes/{note}/edit', function (Note $note) {
        return Inertia::render('Notes/Update', [
            'note' => $note->load([
                'user',
                'notable',
            ]),
            'notableTypes' => NotableRegistry::keys(),
        ]);
    })->name('notes.edit');

    /**
     * -------------------------------------
     * -------------- Orders ---------------
     * -------------------------------------
     */
    Route::get('/orders', function (
        Request $request,
        OrderQueryService $service
    ) {
        return Inertia::render('Orders/Index', [
            'orders' => $service->list($request),
        ]);
    })->name('orders.index');

    Route::get('/orders/create', function () {
        return Inertia::render('Orders/Create', [
            'users' => User::orderBy('name')->get(['id', 'name']),
            'deals' => Deal::orderBy('title')->get(['id', 'title']),
            'products' => Product::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('orders.create');

    Route::get('/orders/{order}', function (
        Order $order,
        OrderQueryService $service
    ) {
        return Inertia::render('Orders/Show', [
            'order' => $service->show($order),
        ]);
    })->name('orders.show');

    Route::get('/orders/{order}/edit', function (Order $order) {
        return Inertia::render('Orders/Update', [
            'order' => $order->load([
                'user',
                'deal',
                'products',
            ]),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'deals' => Deal::orderBy('title')->get(['id', 'title']),
            'products' => Product::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('orders.edit');

    Route::get('/orders/{order}/success', function (
        Order $order,
        OrderQueryService $service
    ) {
        return Inertia::render('Orders/Success', [
            'order' => $service->show($order),
        ]);
    })->name('orders.success');

    /**
     * -----------------------------------------
     * ------------- Order Products ------------
     * -----------------------------------------
     */
    Route::get('/orders/{order}/products', function (Order $order) {
        return Inertia::render('OrderProducts/Index', [
            'order' => $order->load([
                'products',
            ]),
        ]);
    })->name('orders.products.index');

    Route::get('/orders/{order}/products/add', function (Order $order) {
        return Inertia::render('OrderProducts/Add', [
            'order' => $order,
            'products' => Product::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('orders.products.add');

    Route::get(
        '/orders/{order}/products/{product}/edit',
        function (Order $order, Product $product) {
            $product = $order->products()
                ->where('products.id', $product->id)
                ->firstOrFail();
            return Inertia::render('OrderProducts/Edit', [
                'order' => $order,
                'product' => $product,
            ]);
        }
    )->name('orders.products.edit');

    /**
     * -----------------------------------------
     * --------------- Parts -------------------
     * -----------------------------------------
     */
    Route::get('/parts', function (
        Request $request,
        PartQueryService $service
    ) {
        return Inertia::render('Parts/Index', [
            'parts' => $service->list($request),
        ]);
    })->name('parts.index');

    Route::get('/parts/create', function () {
        return Inertia::render('Parts/Create', [
            'categories' => PartCategory::orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('parts.create');

    Route::get('/parts/stock', function (
        Request $request,
        PartQueryService $service
    ) {
        return Inertia::render('Parts/Stock/Index', [
            'parts' => Part::select(
                'id',
                'name',
                'sku',
                'quantity',
                'reorder_point'
            )->orderBy('quantity')
                ->paginate(25)
                ->withQueryString(),
        ]);
    })->name('parts.stock.index');

    Route::get('/parts/stock/low', function (Request $request) {
        return Inertia::render('Parts/Stock/LowStock', [
            'parts' => Part::select(
                'id',
                'name',
                'sku',
                'quantity',
                'reorder_point'
            )->lowStock()
                ->orderBy('quantity')
                ->paginate(25)
                ->withQueryString(),
        ]);
    })->name('parts.stock.low');

    Route::get('/parts/{part}', function (
        Part $part,
        PartQueryService $service
    ) {
        return Inertia::render('Parts/Show', [
            'part' => $service->show($part),
        ]);
    })->name('parts.show');

    Route::get('/parts/{part}/edit', function (Part $part) {
        return Inertia::render('Parts/Update', [
            'part' => $part->load([
                'product',
                'category',
                'primarySupplier',
                'primaryImage',
                'billOfMaterials',
            ]),
            'categories' => PartCategory::orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('parts.edit');

    /**
     * -------------------------------------------------------
     * ------- Bill Of Materials (nested: parts) -------------
     * -------------------------------------------------------
     */
    Route::get('/parts/{part}/bill-of-materials', function (
        Part $part,
        Request $request,
        BillOfMaterialQueryService $service
    ) {
        return Inertia::render('BillOfMaterials/Index', [
            'part' => $part,
            'billOfMaterials' => $service->list($part, $request),
        ]);
    })->name('parts.billOfMaterials.index');

    Route::get('/parts/{part}/bill-of-materials/create', function (Part $part) {
        return Inertia::render('BillOfMaterials/Create', [
            'part' => $part,
            'parts' => Part::orderBy('name')->get(['id', 'sku', 'name']),
        ]);
    })->name('parts.billOfMaterials.create');

    Route::get(
        '/parts/{part}/bill-of-materials/{billOfMaterial}',
        function (Part $part, BillOfMaterial $billOfMaterial) {
            return Inertia::render('BillOfMaterials/Show', [
                'part' => $part,
                'billOfMaterial' => $billOfMaterial->load([
                    'childPart',
                    'creator',
                ]),
            ]);
        }
    )->name('parts.billOfMaterials.show');

    Route::get(
        '/parts/{part}/bill-of-materials/{billOfMaterial}/edit',
        function (Part $part, BillOfMaterial $billOfMaterial) {
            return Inertia::render('BillOfMaterials/Update', [
                'part' => $part,
                'billOfMaterial' => $billOfMaterial->load([
                    'childPart',
                    'creator',
                ]),
                'parts' => Part::orderBy('name')->get(['id', 'sku', 'name']),
            ]);
        }
    )->name('parts.billOfMaterials.edit');

    /**
     * -------------------------------------------------------
     * -------- Part Serial Numbers (nested: parts) ----------
     * -------------------------------------------------------
     */
    Route::get('/parts/{part}/serial-numbers', function (
        Part $part,
        Request $request,
        PartSerialNumberQueryService $service
    ) {
        return Inertia::render('PartSerialNumbers/Index', [
            'part' => $part,
            'serialNumbers' => $service->list($request, $part),
        ]);
    })->name('parts.serialNumbers.index');

    Route::get('/parts/{part}/serial-numbers/create', function (Part $part) {
        return Inertia::render('PartSerialNumbers/Create', [
            'part' => $part,
        ]);
    })->name('parts.serialNumbers.create');

    Route::get(
        '/parts/{part}/serial-numbers/{serialNumber}/edit',
        function (Part $part, PartSerialNumber $serialNumber) {
            return Inertia::render('PartSerialNumbers/Update', [
                'part' => $part,
                'serialNumber' => $serialNumber,
            ]);
        }
    )->name('parts.serialNumbers.edit');

    /**
     * -------------------------------------------------------
     * ------------ Part Stock (nested: parts) ---------------
     * -------------------------------------------------------
     */
    Route::get('/parts/{part}/stock', function (
        Part $part,
        PartStockMovementQueryService $service,
        Request $request
    ) {
        return Inertia::render('Parts/Stock/Show', [
            'part' => $part->only(
                'id',
                'name',
                'sku',
                'quantity',
                'reorder_point'
            ),
            'movements' => $service->list($request, $part),
        ]);
    })->name('parts.stock.show');

    /**
     * -------------------------------------------------------
     * ------- Part Stock Movements (nested: parts) ----------
     * -------------------------------------------------------
     */
    Route::get('/parts/{part}/stock-movements', function (
        Part $part,
        Request $request,
        PartStockMovementQueryService $service
    ) {
        return Inertia::render('PartStockMovements/Index', [
            'part' => $part,
            'stockMovements' => $service->list($request, $part),
        ]);
    })->name('parts.stockMovements.index');

    Route::get('/parts/{part}/stock-movements/create', function (Part $part) {
        return Inertia::render('PartStockMovements/Create', [
            'part' => $part,
        ]);
    })->name('parts.stockMovements.create');

    Route::get(
        '/parts/{part}/stock-movements/{stockMovement}',
        function (Part $part, PartStockMovement $stockMovement) {
            return Inertia::render('PartStockMovements/Show', [
                'part' => $part,
                'stockMovement' => $stockMovement->load([
                    'part',
                    'createdBy',
                ]),
            ]);
        }
    )->name('parts.stockMovements.show');

    /**
     * -----------------------------------------
     * ----------- Part Categories -------------
     * -----------------------------------------
     */
    Route::get('/part-categories', function (
        Request $request,
        PartCategoryQueryService $service
    ) {
        return Inertia::render('PartCategories/Index', [
            'partCategories' => $service->list($request),
        ]);
    })->name('part-categories.index');

    Route::get('/part-categories/create', function () {
        return Inertia::render('PartCategories/Create', [
            'categories' => PartCategory::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('part-categories.create');

    Route::get('/part-categories/{partCategory}', function (
        PartCategory $partCategory,
        PartCategoryQueryService $service
    ) {
        return Inertia::render('PartCategories/Show', [
            'partCategory' => $service->show($partCategory),
        ]);
    })->name('part-categories.show');

    Route::get('/part-categories/{partCategory}/edit', function (
        PartCategory $partCategory
    ) {
        return Inertia::render('PartCategories/Update', [
            'partCategory' => $partCategory->load([
                'parent',
                'children',
                'parts',
            ]),
            'categories' => PartCategory::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('part-categories.edit');

    /**
     * -----------------------------------------
     * ------------- Part Images ---------------
     * -----------------------------------------
     */
    Route::get('/part-images', function (
        Request $request,
        PartImageQueryService $service
    ) {
        return Inertia::render('PartImages/Index', [
            'partImages' => $service->list($request),
        ]);
    })->name('part-images.index');

    Route::get('/part-images/create', function () {
        return Inertia::render('PartImages/Create', [
            'parts' => Part::orderBy('name')->get(['id', 'sku', 'name']),
        ]);
    })->name('part-images.create');

    Route::get('/part-images/{partImage}', function (
        PartImage $partImage,
        PartImageQueryService $service
    ) {
        return Inertia::render('PartImages/Show', [
            'partImage' => $service->show($partImage),
        ]);
    })->name('part-images.show');

    Route::get('/part-images/{partImage}/edit', function (
        PartImage $partImage
    ) {
        return Inertia::render('PartImages/Update', [
            'partImage' => $partImage->load([
                'part',
            ]),
            'parts' => Part::orderBy('name')->get(['id', 'sku', 'name']),
        ]);
    })->name('part-images.edit');

    /**
     * -----------------------------------------
     * ------------- Permissions ---------------
     * -----------------------------------------
     */
    Route::get('/permissions', function (
        Request $request,
        PermissionQueryService $service
    ) {
        return Inertia::render('Permissions/Index', [
            'permissions' => $service->list($request),
        ]);
    })->name('permissions.index');

    Route::get('/permissions/create', function () {
        return Inertia::render('Permissions/Create', [
            'roles' => Role::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('permissions.create');

    Route::get('/permissions/{permission}', function (
        Permission $permission,
        PermissionQueryService $service
    ) {
        return Inertia::render('Permissions/Show', [
            'permission' => $service->show($permission),
        ]);
    })->name('permissions.show');

    Route::get('/permissions/{permission}/edit', function (
        Permission $permission
    ) {
        return Inertia::render('Permissions/Update', [
            'permission' => $permission->load([
                'roles',
            ]),
            'roles' => Role::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('permissions.edit');

    /**
     * -----------------------------------------
     * -------------- Pipelines ----------------
     * -----------------------------------------
     */
    Route::get('/pipelines', function (
        Request $request,
        PipelineQueryService $service
    ) {
        return Inertia::render('Pipelines/Index', [
            'pipelines' => $service->list($request),
        ]);
    })->name('pipelines.index');

    Route::get('/pipelines/create', function () {
        return Inertia::render('Pipelines/Create');
    })->name('pipelines.create');

    Route::get('/pipelines/{pipeline}', function (
        Pipeline $pipeline,
        PipelineQueryService $service
    ) {
        return Inertia::render('Pipelines/Show', [
            'pipeline' => $service->show($pipeline),
        ]);
    })->name('pipelines.show');

    Route::get('/pipelines/{pipeline}/edit', function (Pipeline $pipeline) {
        return Inertia::render('Pipelines/Update', [
            'pipeline' => $pipeline->load([
                'stages',
            ]),
        ]);
    })->name('pipelines.edit');

    /**
     * -------------------------------------------------------
     * ------------------- Pipeline Stages -------------------
     * -------------------------------------------------------
     */
    Route::get('/pipeline-stages', function (
        Request $request,
        PipelineStageQueryService $service
    ) {
        return Inertia::render('PipelineStages/Index', [
            'pipelineStages' => $service->list($request),
        ]);
    })->name('pipeline-stages.index');

    Route::get('/pipeline-stages/create', function () {
        return Inertia::render('PipelineStages/Create', [
            'pipelines' => Pipeline::orderBy('id')->get(['id', 'name']),
            'deals' => Deal::orderBy('id')->get(['id', 'name']),
        ]);
    })->name('pipeline-stages.create');

    Route::get('/pipeline-stages/{pipelineStage}', function (
        PipelineStage $pipelineStage,
        PipelineStageQueryService $service
    ) {
        return Inertia::render('PipelineStages/Show', [
            'pipelineStage' => $service->show($pipelineStage),
        ]);
    })->name('pipeline-stages.show');

    Route::get('/pipeline-stages/{pipelineStage}/edit', function (
        PipelineStage $pipelineStage
    ) {
        return Inertia::render('PipelineStages/Update', [
            'stage' => $pipelineStage->load('pipeline'),
            'pipeline' => $pipelineStage->pipeline,
            'pipelines' => Pipeline::orderBy('id')->get(['id', 'name']),
            'deals' => Invoice::orderBy('id')->get(['id', 'title as name']),
        ]);
    })->name('pipeline-stages.edit');

    /**
     * -----------------------------------------
     * -------------- Products -----------------
     * -----------------------------------------
     */
    Route::get('/products', function (
        Request $request,
        ProductQueryService $service
    ) {
        return Inertia::render('Products/Index', [
            'products' => $service->list($request),
        ]);
    })->name('products.index');

    Route::get('/products/create', function () {
        return Inertia::render('Products/Create');
    })->name('products.create');

    Route::get('/products/{product}', function (
        Product $product,
        ProductQueryService $service
    ) {
        return Inertia::render('Products/Show', [
            'product' => $service->show($product),
        ]);
    })->name('products.show');

    Route::get('/products/{product}/edit', function (Product $product) {
        return Inertia::render('Products/Update', [
            'product' => $product->load([
                'creator',
            ]),
        ]);
    })->name('products.edit');

    /**
     * -------------------------------------------------------
     * -------- Product Deals (nested: products) -------------
     * -------------------------------------------------------
     */
    Route::get('/products/{product}/deals', function (Product $product) {
        return Inertia::render('ProductDeals/Index', [
            'product' => $product->load([
                'deals',
            ]),
        ]);
    })->name('products.deals.index');

    Route::get('/products/{product}/deals/add', function (Product $product) {
        return Inertia::render('ProductDeals/Add', [
            'product' => $product,
            'deals' => Deal::orderBy('title')->get(['id', 'title']),
        ]);
    })->name('products.deals.add');

    Route::get(
        '/products/{product}/deals/{deal}/edit',
        function (Product $product, Deal $deal) {
            $deal = $product->deals()
                ->where('deals.id', $deal->id)
                ->firstOrFail();
            return Inertia::render('ProductDeals/Edit', [
                'deal' => $deal,
                'product' => $product,
            ]);
        }
    )->name('products.deals.edit');

    /**
     * -------------------------------------------------------
     * -------- Product Orders (nested: products) ------------
     * -------------------------------------------------------
     */
    Route::get('/products/{product}/orders', function (Product $product) {
        return Inertia::render('ProductOrders/Index', [
            'product' => $product->load([
                'orders',
            ]),
        ]);
    })->name('products.orders.index');

    Route::get('/products/{product}/orders/add', function (Product $product) {
        return Inertia::render('ProductOrders/Add', [
            'product' => $product,
            'orders' => Order::orderBy('id')->get(['id']),
        ]);
    })->name('products.orders.add');

    Route::get(
        '/products/{product}/orders/{order}/edit',
        function (Product $product, Order $order) {
            $order = $product->orders()
                ->where('orders.id', $order->id)
                ->firstOrFail();
            return Inertia::render('ProductOrders/Edit', [
                'product' => $product,
                'order' => $order,
            ]);
        }
    )->name('products.orders.edit');

    /**
     * -------------------------------------------------------
     * -------- Product Quotes (nested: products) ------------
     * -------------------------------------------------------
     */
    Route::get('/products/{product}/quotes', function (Product $product) {
        return Inertia::render('ProductQuotes/Index', [
            'product' => $product->load([
                'quotes',
            ]),
        ]);
    })->name('products.quotes.index');

    Route::get('/products/{product}/quotes/add', function (Product $product) {
        return Inertia::render('ProductQuotes/Add', [
            'product' => $product,
            'quotes' => Quote::orderBy('id')->get(['id']),
        ]);
    })->name('products.quotes.add');

    Route::get(
        '/products/{product}/quotes/{quote}/edit',
        function (Product $product, Quote $quote) {
            $quote = $product->quotes()
                ->where('quotes.id', $quote->id)
                ->firstOrFail();
            return Inertia::render('ProductQuotes/Edit', [
                'product' => $product,
                'quote' => $quote,
            ]);
        }
    )->name('products.quotes.edit');

    /**
     * -----------------------------------------
     * --------------- Quotes ------------------
     * -----------------------------------------
     */
    Route::get('/quotes', function (
        Request $request,
        QuoteQueryService $service
    ) {
        return Inertia::render('Quotes/Index', [
            'quotes' => $service->list($request),
        ]);
    })->name('quotes.index');

    Route::get('/quotes/create', function () {
        return Inertia::render('Quotes/Create', [
            'deals' => Deal::orderBy('title')->get(['id', 'title']),
            'products' => Product::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('quotes.create');

    Route::get('/quotes/{quote}', function (
        Quote $quote,
        QuoteQueryService $service
    ) {
        return Inertia::render('Quotes/Show', [
            'quote' => $service->show($quote),
        ]);
    })->name('quotes.show');

    Route::get('/quotes/{quote}/edit', function (Quote $quote) {
        return Inertia::render('Quotes/Update', [
            'quote' => $quote->load([
                'deal',
                'products',
                'creator',
            ]),
            'deals' => Deal::orderBy('title')->get(['id', 'title']),
            'products' => Product::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('quotes.edit');

    /**
     * -------------------------------------------------------
     * -------- Quote Products (nested: quotes) --------------
     * -------------------------------------------------------
     */
    Route::get('/quotes/{quote}/products', function (Quote $quote) {
        return Inertia::render('QuoteProducts/Index', [
            'quote' => $quote->load([
                'products',
            ]),
        ]);
    })->name('quotes.products.index');

    Route::get('/quotes/{quote}/products/add', function (Quote $quote) {
        return Inertia::render('QuoteProducts/Add', [
            'quote' => $quote,
            'products' => Product::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('quotes.products.add');

    Route::get(
        '/quotes/{quote}/products/{product}/edit',
        function (Quote $quote, Product $product) {
            $product = $quote->products()
                ->where('products.id', $product->id)
                ->firstOrFail();
            return Inertia::render('QuoteProducts/Edit', [
                'quote' => $quote,
                'product' => $product,
            ]);
        }
    )->name('quotes.products.edit');

    /**
     * -----------------------------------------
     * --------------- Roles -------------------
     * -----------------------------------------
     */
    Route::get('/roles', function (
        Request $request,
        RoleQueryService $service
    ) {
        return Inertia::render('Roles/Index', [
            'roles' => $service->list($request),
        ]);
    })->name('roles.index');

    Route::get('/roles/{role}', function (
        Role $role,
        RoleQueryService $service
    ) {
        return Inertia::render('Roles/Show', [
            'role' => $service->show($role),
        ]);
    })->name('roles.show');

    Route::get('/roles/{role}/edit', function (Role $role) {
        return Inertia::render('Roles/Update', [
            'role' => $role->load([
                'permissions',
                'users',
            ]),
            'permissions' => Permission::orderBy('name')->get(['id', 'name']),
        ]);
    })->name('roles.edit');

    /**
     * -----------------------------------------
     * -------------- Suppliers ----------------
     * -----------------------------------------
     */
    Route::get('/suppliers', function (
        Request $request,
        SupplierQueryService $service
    ) {
        return Inertia::render('Suppliers/Index', [
            'suppliers' => $service->list($request),
        ]);
    })->name('suppliers.index');

    Route::get('/suppliers/create', function () {
        return Inertia::render('Suppliers/Create');
    })->name('suppliers.create');

    Route::get('/suppliers/{supplier}', function (
        Supplier $supplier,
        SupplierQueryService $service
    ) {
        return Inertia::render('Suppliers/Show', [
            'supplier' => $service->show($supplier),
        ]);
    })->name('suppliers.show');

    Route::get('/suppliers/{supplier}/edit', function (Supplier $supplier) {
        return Inertia::render('Suppliers/Update', [
            'supplier' => $supplier->load([
                'parts',
                'partSuppliers',
            ]),
        ]);
    })->name('suppliers.edit');

    /**
     * -----------------------------------------
     * --------------- Tasks -------------------
     * -----------------------------------------
     */
    Route::get('/tasks', function (
        Request $request,
        TaskQueryService $service
    ) {
        return Inertia::render('Tasks/Index', [
            'tasks' => $service->list($request),
        ]);
    })->name('tasks.index');

    Route::get('/tasks/create', function () {
        return Inertia::render('Tasks/Create', [
            'users' => User::orderBy('name')->get(['id', 'name']),
            'taskableTypes' => TaskableRegistry::keys(),
        ]);
    })->name('tasks.create');

    Route::get('/tasks/{task}', function (
        Task $task,
        TaskQueryService $service
    ) {
        return Inertia::render('Tasks/Show', [
            'task' => $service->show($task),
        ]);
    })->name('tasks.show');

    Route::get('/tasks/{task}/edit', function (Task $task) {
        return Inertia::render('Tasks/Update', [
            'task' => $task->load([
                'assignee',
                'creator',
                'taskable',
            ]),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'taskableTypes' => TaskableRegistry::keys(),
        ]);
    })->name('tasks.edit');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
