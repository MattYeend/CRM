<?php

use App\Models\Part;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'suppliers.view.all',
        'suppliers.create',
        'suppliers.update.any',
        'suppliers.delete.any',
        'suppliers.restore.any',
    ];

    // Create permissions in DB
    $permissionModels = collect($permissions)
        ->map(fn($name) => Permission::firstOrCreate(['name' => $name]));

    // Create admin role and attach permissions
    $role = Role::factory()->create(['name' => 'admin']);
    $role->permissions()->sync($permissionModels->pluck('id'));

    // Attach role to the user
    $this->auth->update([
        'role_id' => $role->id,
    ]);

    // Authenticate the user
    $this->actingAs($this->auth, 'sanctum');

    // Disable throttling for tests
    $this->withoutMiddleware(ThrottleRequests::class);
});

test('index returns paginated suppliers', function () {
    Supplier::factory()->count(12)->create();

    $response = $this->getJson(route('api.suppliers.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('index returns all suppliers when no pagination specified', function () {
    Supplier::factory()->count(3)->create();

    $response = $this->getJson(route('api.suppliers.index'));

    $response->assertStatus(200);
    $this->assertCount(3, $response->json('data'));
});

test('index returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.suppliers.index'));

    $response->assertStatus(403);
});

test('index filters suppliers by search term', function () {
    Supplier::factory()->create(['name' => 'Acme Corp']);
    Supplier::factory()->create(['name' => 'Beta Supplies']);

    $response = $this->getJson(route('api.suppliers.index', ['search' => 'Acme']));

    $response->assertStatus(200);
    $this->assertCount(1, $response->json('data'));
    $response->assertJsonFragment(['name' => 'Acme Corp']);
});

test('index filters suppliers by active status', function () {
    Supplier::factory()->count(3)->create(['is_active' => true]);
    Supplier::factory()->count(2)->create(['is_active' => false]);

    $response = $this->getJson(route('api.suppliers.index', ['is_active' => true]));

    $response->assertStatus(200);
    $this->assertCount(3, $response->json('data'));
});

test('show returns a single supplier', function () {
    $supplier = Supplier::factory()->create();

    $response = $this->getJson(route('api.suppliers.show', $supplier));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $supplier->id, 'name' => $supplier->name]);
    $response->assertJsonStructure([
        'id',
        'name',
        'code',
        'email',
        'phone',
        'currency',
        'is_active',
    ]);
});

test('show returns 404 for non-existent supplier', function () {
    $response = $this->getJson(route('api.suppliers.show', 999999));

    $response->assertStatus(404);
});

test('show returns 403 when user lacks permission', function () {
    $supplier = Supplier::factory()->create();
    $user     = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.suppliers.show', $supplier));

    $response->assertStatus(403);
});

test('store creates a new supplier and returns 201', function () {
    $payload = [
        'name' => 'Test Supplier Ltd',
        'code' => 'SUP-TEST-001',
        'email' => 'contact@testsupplier.com',
        'phone' => '01782 000000',
        'currency' => 'GBP',
        'payment_terms' => 'NET30',
        'contact_name' => 'John Smith',
        'contact_email' => 'john@testsupplier.com',
        'is_active' => true,
    ];

    $response = $this->postJson(route('api.suppliers.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['name' => 'Test Supplier Ltd', 'code' => 'SUP-TEST-001']);
    $this->assertDatabaseHas('suppliers', [
        'name' => 'Test Supplier Ltd',
        'code' => 'SUP-TEST-001',
    ]);
});

test('store returns 422 when required fields are missing', function () {
    $response = $this->postJson(route('api.suppliers.store'), []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name']);
});

test('store returns 422 when code is not unique', function () {
    Supplier::factory()->create(['code' => 'SUP-DUPE-001']);

    $payload = [
        'name' => 'Another Supplier',
        'code' => 'SUP-DUPE-001',
    ];

    $response = $this->postJson(route('api.suppliers.store'), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('code');
});

test('store returns 422 when email is invalid', function () {
    $payload = [
        'name' => 'Bad Email Supplier',
        'email' => 'not-a-valid-email',
    ];

    $response = $this->postJson(route('api.suppliers.store'), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('email');
});

test('store returns 422 when website is invalid', function () {
    $payload = [
        'name' => 'Bad Website Supplier',
        'website' => 'not-a-valid-url',
    ];

    $response = $this->postJson(route('api.suppliers.store'), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('website');
});

test('store returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.suppliers.store'), []);

    $response->assertStatus(403);
});

test('store sets created_by to the authenticated user', function () {
    $payload = [
        'name' => 'Audited Supplier',
    ];

    $this->postJson(route('api.suppliers.store'), $payload);

    $this->assertDatabaseHas('suppliers', [
        'name' => 'Audited Supplier',
        'created_by' => $this->auth->id,
    ]);
});

test('update modifies an existing supplier', function () {
    $supplier = Supplier::factory()->create([
        'name' => 'Old Supplier Name',
        'email' => 'old@supplier.com',
    ]);

    $payload = [
        'name' => 'Updated Supplier Name',
        'email' => 'new@supplier.com',
    ];

    $response = $this->putJson(route('api.suppliers.update', $supplier), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['name' => 'Updated Supplier Name', 'email' => 'new@supplier.com']);
    $this->assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'name' => 'Updated Supplier Name',
        'email' => 'new@supplier.com',
    ]);
});

test('update returns 404 for non-existent supplier', function () {
    $response = $this->putJson(route('api.suppliers.update', 999999), ['name' => 'Ghost']);

    $response->assertStatus(404);
});

test('update returns 403 when user lacks permission', function () {
    $supplier = Supplier::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->putJson(route('api.suppliers.update', $supplier), ['name' => 'Sneaky']);

    $response->assertStatus(403);
});

test('update returns 422 when code is not unique to another supplier', function () {
    Supplier::factory()->create(['code' => 'SUP-TAKEN-001']);
    $supplier = Supplier::factory()->create(['code' => 'SUP-OWN-001']);

    $response = $this->putJson(route('api.suppliers.update', $supplier), [
        'code' => 'SUP-TAKEN-001',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('code');
});

test('update allows supplier to keep its own code', function () {
    $supplier = Supplier::factory()->create(['code' => 'SUP-OWN-001']);

    $response = $this->putJson(route('api.suppliers.update', $supplier), [
        'code' => 'SUP-OWN-001',
        'name' => 'Updated Name Only',
    ]);

    $response->assertStatus(200);
});

test('update sets updated_by to the authenticated user', function () {
    $supplier = Supplier::factory()->create();

    $this->putJson(route('api.suppliers.update', $supplier), ['name' => 'Audit Check']);

    $this->assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'updated_by' => $this->auth->id,
    ]);
});

test('destroy soft deletes a supplier and returns 204', function () {
    $supplier = Supplier::factory()->create();

    $response = $this->deleteJson(route('api.suppliers.destroy', $supplier));

    $response->assertStatus(204);
    $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
});

test('destroy returns 404 for non-existent supplier', function () {
    $response = $this->deleteJson(route('api.suppliers.destroy', 999999));

    $response->assertStatus(404);
});

test('destroy returns 403 when user lacks permission', function () {
    $supplier = Supplier::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->deleteJson(route('api.suppliers.destroy', $supplier));

    $response->assertStatus(403);
});

test('destroy sets deleted_by to the authenticated user', function () {
    $supplier = Supplier::factory()->create();

    $this->deleteJson(route('api.suppliers.destroy', $supplier));

    $this->assertSoftDeleted('suppliers', [
        'id' => $supplier->id,
        'deleted_by' => $this->auth->id,
    ]);
});

test('restore recovers a soft deleted supplier', function () {
    $supplier = Supplier::factory()->create(['created_by' => $this->auth->id]);
    $supplier->delete();

    $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);

    $response = $this->postJson(route('api.suppliers.restore', $supplier->id));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $supplier->id]);
    $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'deleted_at' => null]);
});

test('restore returns 404 for non-existent supplier', function () {
    $response = $this->postJson(route('api.suppliers.restore', 999999));

    $response->assertStatus(404);
});

test('restore returns 403 when user lacks permission', function () {
    $supplier = Supplier::factory()->create();
    $supplier->delete();

    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.suppliers.restore', $supplier->id));

    $response->assertStatus(403);
});

test('restore returns 404 when supplier is not deleted', function () {
    $supplier = Supplier::factory()->create();

    $response = $this->postJson(route('api.suppliers.restore', $supplier->id));

    $response->assertStatus(404);
});

test('restore sets restored_by to the authenticated user', function () {
    $supplier = Supplier::factory()->create(['created_by' => $this->auth->id]);
    $supplier->delete();

    $this->postJson(route('api.suppliers.restore', $supplier->id));

    $this->assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'restored_by' => $this->auth->id,
    ]);
});