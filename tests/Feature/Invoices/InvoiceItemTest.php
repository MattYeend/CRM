<?php

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'invoices.view.all',
        'invoices.create',
        'invoices.update.any',
        'invoices.delete.any',
        'invoice.restore.any',
        'invoiceItems.view.all',
        'invoiceItems.create',
        'invoiceItems.update.any',
        'invoiceItems.delete.any',
        'invoiceItems.restore.any',
    ];

    // Create permissions in DB
    $permissionModels = collect($permissions)
        ->map(fn($name) => Permission::firstOrCreate(['name' => $name]));

    // Create admin role and attach permissions
    $role = Role::factory()->create(['name' => 'admin']);
    $role->permissions()->sync($permissionModels->pluck('id'));

    // Attach role to the user
    $this->auth->update([
        'role_id' => $role->id
    ]);

    // Authenticate the user
    $this->actingAs($this->auth, 'sanctum');

    // Disable throttling for tests
    $this->withoutMiddleware(ThrottleRequests::class);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Index ---------------------------
 * -------------------------------------------------------------
 */
test('index returns paginated invoice items with relations', function () {
    $invoice = Invoice::factory()->create();
    $product = Product::factory()->create();

    InvoiceItem::factory()->count(12)->create([
        'invoice_id' => $invoice->id,
        'product_id' => $product->id,
    ]);

    $response = $this->getJson(route('api.invoiceItems.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));

    // Ensure relations are loaded
    foreach ($response->json('data') as $item) {
        $this->assertArrayHasKey('invoice', $item);
        $this->assertArrayHasKey('product', $item);
    }
});

test('index returns all invoiceItems when no pagination specified', function () {
    $invoice = Invoice::factory()->create();
    $product = Product::factory()->create();
    InvoiceItem::factory()->create([
        'invoice_id'  => $invoice->id,
        'product_id'  => $product->id,
        'created_by'  => $this->auth->id,
    ]);

    $response = $this->getJson(route('api.invoiceItems.index'));

    $response->assertStatus(200);
    $this->assertCount(1, $response->json('data'));
});

test('index returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.invoiceItems.index'));

    $response->assertStatus(403);
});

/**
 * ----------------------------------------------------------
 * -------------------------- Show --------------------------
 * ----------------------------------------------------------
 */
test('show returns an invoice item with relations loaded', function () {
    $invoice = Invoice::factory()->create();
    $product = Product::factory()->create();

    $invoiceItem = InvoiceItem::factory()->create([
        'invoice_id' => $invoice->id,
        'product_id' => $product->id,
    ]);

    $response = $this->getJson(route('api.invoiceItems.show', $invoiceItem));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $invoiceItem->id]);

    $response->assertJsonStructure([
        'id',
        'invoice_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'line_total',
        'invoice' => [],
        'product' => [],
    ]);
});

test('show returns 403 when user lacks permission', function () {
    $invoice = Invoice::factory()->create();
    $product = Product::factory()->create();
    $invoiceItem = InvoiceItem::factory()->create([
        'invoice_id'  => $invoice->id,
        'product_id'  => $product->id,
        'created_by'  => $this->auth->id,
    ]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.invoiceItems.show', $invoiceItem->id));

    $response->assertStatus(403);
});

test('show returns 404 for non-existent invoiceItem', function () {
    $response = $this->getJson(route('api.invoiceItems.show', 999999));

    $response->assertStatus(404);
});

/**
 * ---------------------------------------------------------
 * ------------------------- Store -------------------------
 * ---------------------------------------------------------
 */
test('store creates a new invoice item and returns 201', function () {
    $invoice = Invoice::factory()->create();
    $product = Product::factory()->create();

    $payload = [
        'invoice_id' => $invoice->id,
        'product_id' => $product->id,
        'description' => 'Test Item',
        'quantity' => 3,
        'unit_price' => 50,
    ];

    $response = $this->postJson(route('api.invoiceItems.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment([
        'description' => 'Test Item',
        'quantity' => 3,
        'unit_price' => 50,
        'line_total' => 150,
    ]);

    $this->assertDatabaseHas('invoice_items', ['description' => 'Test Item']);
});

test('store returns 403 when user lacks permission', function () {
    $invoice = Invoice::factory()->create();
    $product = Product::factory()->create();
    $invoiceItem = InvoiceItem::factory()->create([
        'invoice_id'  => $invoice->id,
        'product_id'  => $product->id,
        'created_by'  => $this->auth->id,
    ]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.invoiceItems.store', $invoiceItem->id));

    $response->assertStatus(403);
});

/**
 * ----------------------------------------------------------
 * ------------------------- Update -------------------------
 * ----------------------------------------------------------
 */
test('update modifies an existing invoice item', function () {
    $invoice = Invoice::factory()->create();
    $product = Product::factory()->create();

    $invoiceItem = InvoiceItem::factory()->create([
        'invoice_id' => $invoice->id,
        'product_id' => $product->id,
        'description' => 'Old Item',
        'quantity' => 2,
        'unit_price' => 25,
        'line_total' => 50,
    ]);

    $payload = [
        'description' => 'Updated Item',
        'quantity' => 4,
        'unit_price' => 30,
    ];

    $response = $this->putJson(route('api.invoiceItems.update', $invoiceItem), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'description' => 'Updated Item',
        'quantity' => 4,
        'unit_price' => 30,
        'line_total' => 120,
    ]);

    $this->assertDatabaseHas('invoice_items', [
        'id' => $invoiceItem->id,
        'description' => 'Updated Item',
    ]);
});

test('update returns 403 when user lacks permission', function () {
    $invoice = Invoice::factory()->create();
    $product = Product::factory()->create();
    $invoiceItem = InvoiceItem::factory()->create([
        'invoice_id'  => $invoice->id,
        'product_id'  => $product->id,
        'created_by'  => $this->auth->id,
    ]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.invoiceItems.update', $invoiceItem->id));

    $response->assertStatus(403);
});

test('update returns 404 for non-existent invoiceItem', function () {
    $response = $this->putJson(route('api.invoiceItems.update', 999999), ['name' => 'Ghost']);

    $response->assertStatus(404);
});

/**
 * -----------------------------------------------------------
 * ------------------------- Destroy -------------------------
 * -----------------------------------------------------------
 */
test('destroy deletes an invoice item', function () {
    $invoice = Invoice::factory()->create();
    $product = Product::factory()->create();

    $invoiceItem = InvoiceItem::factory()->create([
        'invoice_id' => $invoice->id,
        'product_id' => $product->id,
    ]);

    $response = $this->deleteJson(route('api.invoiceItems.destroy', $invoiceItem));

    $response->assertStatus(204);
    $this->assertSoftDeleted('invoice_items', ['id' => $invoiceItem->id]);
});

test('destroy returns 403 when user lacks permission', function () {
    $invoice = Invoice::factory()->create();
    $product = Product::factory()->create();
    $invoiceItem = InvoiceItem::factory()->create([
        'invoice_id'  => $invoice->id,
        'product_id'  => $product->id,
        'created_by'  => $this->auth->id,
    ]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.invoiceItems.destroy', $invoiceItem->id));

    $response->assertStatus(403);
});

test('destroy returns 404 for non-existent invoiceItem', function () {
    $response = $this->deleteJson(route('api.invoiceItems.destroy', 999999));

    $response->assertStatus(404);
});

/**
 * -----------------------------------------------------------
 * ------------------------- Restore -------------------------
 * -----------------------------------------------------------
 */
test('restore deleted invoice item', function () {
    $invoice = Invoice::factory()->create();
    $product = Product::factory()->create();

    $invoiceItem = InvoiceItem::factory()->create([
        'invoice_id' => $invoice->id,
        'product_id' => $product->id,
    ]);

    $invoiceItem->delete();

    $this->assertSoftDeleted('invoice_items', [
        'id' => $invoiceItem->id,
    ]);

    $response = $this->postJson(route('api.invoiceItems.restore', $invoiceItem->id));

    $response->assertStatus(200);

    $this->assertNotSoftDeleted('invoice_items', [
        'id' => $invoiceItem->id,
    ]);
});

test('restore returns 403 when user lacks permission', function () {
    $invoice = Invoice::factory()->create();
    $product = Product::factory()->create();
    $invoiceItem = InvoiceItem::factory()->create([
        'invoice_id'  => $invoice->id,
        'product_id'  => $product->id,
        'created_by'  => $this->auth->id,
    ]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.invoiceItems.restore', $invoiceItem->id));

    $response->assertStatus(403);
});

test('restore returns 404 for non-existent invoiceItem', function () {
    $response = $this->postJson(route('api.invoiceItems.restore', 999999));

    $response->assertStatus(404);
});

test('restore returns 404 when invoiceItem is not deleted', function () {
    $invoice = Invoice::factory()->create();
    $product = Product::factory()->create();
    $invoiceItem = InvoiceItem::factory()->create([
        'invoice_id'  => $invoice->id,
        'product_id'  => $product->id,
        'created_by'  => $this->auth->id,
    ]);
    $response = $this->postJson(route('api.invoiceItems.restore', $invoiceItem->id));

    $response->assertStatus(404);
});
