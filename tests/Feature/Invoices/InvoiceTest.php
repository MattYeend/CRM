<?php

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Permission;
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
        'invoices.restore.any',
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
test('index returns paginated invoices with relations', function () {
    $company = Company::factory()->create();
    Invoice::factory()->count(12)->create(['company_id' => $company->id]);

    $response = $this->getJson(route('api.invoices.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('index returns all invoices when no pagination specified', function () {
    Invoice::factory()->count(3)->create();

    $response = $this->getJson(route('api.invoices.index'));

    $response->assertStatus(200);
    $this->assertCount(3, $response->json('data'));
});

test('index returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.invoices.index'));

    $response->assertStatus(403);
});

/**
 * ------------------------------------------------------------
 * --------------------------- Show ---------------------------
 * ------------------------------------------------------------
 */
test('show returns an invoice with relations loaded', function () {
    $invoice = Invoice::factory()->create();

    $response = $this->getJson(route('api.invoices.show', $invoice));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $invoice->id]);
    $response->assertJsonStructure([
        'id',
        'number',
        'status',
        'total',
        'currency',
        'company' => [],
        'items' => [],
    ]);
});

test('show returns 403 when user lacks permission', function () {
    $invoice = Invoice::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.invoices.show', $invoice->id));

    $response->assertStatus(403);
});

test('show returns 404 for non-existent invoice', function () {
    $response = $this->getJson(route('api.invoices.show', 999999));

    $response->assertStatus(404);
});

/**
 * ---------------------------------------------------------
 * ------------------------- Store -------------------------
 * ---------------------------------------------------------
 */
test('store creates a new invoice and returns 201', function () {
    $company = Company::factory()->create();

    $payload = [
        'number' => 'INV-1001',
        'company_id' => $company->id,
        'created_by' => $this->auth->id,
        'status' => 'draft',
        'subtotal' => 100,
        'tax' => 10,
        'total' => 110,
        'currency' => 'USD',
    ];

    $response = $this->postJson(route('api.invoices.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['number' => 'INV-1001', 'status' => 'draft']);
    $this->assertDatabaseHas('invoices', ['number' => 'INV-1001', 'created_by' => $this->auth->id]);
});

test('store returns 403 when user lacks permission', function () {
    $invoice = Invoice::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.invoices.store', $invoice->id));

    $response->assertStatus(403);
});

/**
 * ----------------------------------------------------------
 * ------------------------- Update -------------------------
 * ----------------------------------------------------------
 */
test('update modifies an existing invoice', function () {
    $invoice = Invoice::factory()->create(['number' => 'INV-2000', 'status' => 'draft']);

    $payload = [
        'number' => 'INV-2001',
        'status' => 'sent',
    ];

    $response = $this->putJson(route('api.invoices.update', $invoice), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['number' => 'INV-2001', 'status' => 'sent']);
    $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'number' => 'INV-2001', 'status' => 'sent']);
});

test('update returns 403 when user lacks permission', function () {
    $invoice = Invoice::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.invoices.update', $invoice->id));

    $response->assertStatus(403);
});

test('update returns 404 for non-existent invoice', function () {
    $response = $this->putJson(route('api.invoices.update', 999999), ['name' => 'Ghost']);

    $response->assertStatus(404);
});

/**
 * -----------------------------------------------------------
 * ------------------------- Destroy -------------------------
 * -----------------------------------------------------------
 */
test('destroy deletes an invoice', function () {
    $invoice = Invoice::factory()->create();

    $response = $this->deleteJson(route('api.invoices.destroy', $invoice));

    $response->assertStatus(204);

    // If soft deletes are used
    $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);
});

test('destroy returns 403 when user lacks permission', function () {
    $invoice = Invoice::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.invoices.destroy', $invoice->id));

    $response->assertStatus(403);
});

test('destroy returns 404 for non-existent invoice', function () {
    $response = $this->deleteJson(route('api.invoices.destroy', 999999));

    $response->assertStatus(404);
});

/**
 * -----------------------------------------------------------
 * ------------------------- Restore -------------------------
 * -----------------------------------------------------------
 */
test('restore deleted invoice', function () {
    $invoice = Invoice::factory()->create();
    $invoice->delete();

    // Ensure it's soft deleted first
    $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);

    $response = $this->postJson(route('api.invoices.restore', $invoice->id));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $invoice->id]);

    // Now it should not be soft deleted
    $this->assertDatabaseHas('invoices', [
        'id' => $invoice->id,
        'deleted_at' => null,
    ]);
});

test('restore returns 403 when user lacks permission', function () {
    $invoice = Invoice::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.invoices.restore', $invoice->id));

    $response->assertStatus(403);
});

test('restore returns 404 for non-existent invoice', function () {
    $response = $this->postJson(route('api.invoices.restore', 999999));

    $response->assertStatus(404);
});

test('restore returns 404 when invoice is not deleted', function () {
    $invoice = Invoice::factory()->create();

    $response = $this->postJson(route('api.invoices.restore', $invoice->id));

    $response->assertStatus(404);
});
