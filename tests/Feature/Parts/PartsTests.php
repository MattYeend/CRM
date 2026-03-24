<?php

use App\Models\Part;
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
        'parts.view.all',
        'parts.create',
        'parts.update.any',
        'parts.delete.any',
        'parts.restore.any',
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

test('index returns paginated parts', function () {
    Part::factory()->count(12)->create();

    $response = $this->getJson(route('api.parts.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('index returns all parts when no pagination specified', function () {
    Part::factory()->count(3)->create();

    $response = $this->getJson(route('api.parts.index'));

    $response->assertStatus(200);
    $this->assertCount(3, $response->json('data'));
});

test('index returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.parts.index'));

    $response->assertStatus(403);
});

test('show returns a single part', function () {
    $part = Part::factory()->create();

    $response = $this->getJson(route('api.parts.show', $part));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $part->id, 'name' => $part->name]);
    $response->assertJsonStructure([
        'id',
        'sku',
        'name',
        'description',
        'price',
        'currency',
        'quantity',
        'type',
        'status',
    ]);
});

test('show returns 404 for non-existent part', function () {
    $response = $this->getJson(route('api.parts.show', 999999));

    $response->assertStatus(404);
});

test('show returns 403 when user lacks permission', function () {
    $part = Part::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.parts.show', $part));

    $response->assertStatus(403);
});


test('store creates a new part and returns 201', function () {
    $product = Product::factory()->create();

    $payload = [
        'product_id'  => $product->id,
        'sku'         => 'SKU-TEST-001',
        'name'        => 'Test Part',
        'description' => 'A part created during test',
        'price'       => 49.99,
        'currency'    => 'GBP',
        'quantity'    => 10,
        'type'        => 'spare_part',
        'status'      => 'active',
    ];

    $response = $this->postJson(route('api.parts.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['name' => 'Test Part', 'sku' => 'SKU-TEST-001']);
    $this->assertDatabaseHas('parts', ['name' => 'Test Part', 'sku' => 'SKU-TEST-001']);
});

test('store returns 422 when required fields are missing', function () {
    $response = $this->postJson(route('api.parts.store'), []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['sku', 'name', 'description', 'price']);
});

test('store returns 422 when sku is not unique', function () {
    Part::factory()->create(['sku' => 'SKU-DUPE-001']);

    $product = Product::factory()->create();

    $payload = [
        'product_id'  => $product->id,
        'sku'         => 'SKU-DUPE-001',
        'name'        => 'Duplicate Part',
        'description' => 'Should fail',
        'price'       => 10.00,
    ];

    $response = $this->postJson(route('api.parts.store'), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('sku');
});

test('store returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.parts.store'), []);

    $response->assertStatus(403);
});

test('update modifies an existing part', function () {
    $part = Part::factory()->create([
        'name'  => 'Old Part Name',
        'price' => 10.00,
    ]);

    $payload = [
        'name'     => 'Updated Part Name',
        'price'    => 29.99,
        'quantity' => 5,
    ];

    $response = $this->putJson(route('api.parts.update', $part), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['name' => 'Updated Part Name', 'price' => 29.99]);
    $this->assertDatabaseHas('parts', ['id' => $part->id, 'name' => 'Updated Part Name']);
});

test('update returns 404 for non-existent part', function () {
    $response = $this->putJson(route('api.parts.update', 999999), ['name' => 'Ghost']);

    $response->assertStatus(404);
});

test('update returns 403 when user lacks permission', function () {
    $part = Part::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->putJson(route('api.parts.update', $part), ['name' => 'Sneaky']);

    $response->assertStatus(403);
});

test('destroy soft deletes a part and returns 204', function () {
    $part = Part::factory()->create();

    $response = $this->deleteJson(route('api.parts.destroy', $part));

    $response->assertStatus(204);
    $this->assertSoftDeleted('parts', ['id' => $part->id]);
});

test('destroy returns 404 for non-existent part', function () {
    $response = $this->deleteJson(route('api.parts.destroy', 999999));

    $response->assertStatus(404);
});

test('destroy returns 403 when user lacks permission', function () {
    $part = Part::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->deleteJson(route('api.parts.destroy', $part));

    $response->assertStatus(403);
});

test('restore recovers a soft deleted part', function () {
    $part = Part::factory()->create(['created_by' => $this->auth->id]);
    $part->delete();

    $this->assertSoftDeleted('parts', ['id' => $part->id]);

    $response = $this->postJson(route('api.parts.restore', $part->id));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $part->id]);
    $this->assertDatabaseHas('parts', ['id' => $part->id, 'deleted_at' => null]);
});

test('restore returns 404 for non-existent part', function () {
    $response = $this->postJson(route('api.parts.restore', 999999));

    $response->assertStatus(404);
});

test('restore returns 403 when user lacks permission', function () {
    $part = Part::factory()->create();
    $part->delete();

    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.parts.restore', $part->id));

    $response->assertStatus(403);
});

test('restore returns 404 when part is not deleted', function () {
    $part = Part::factory()->create();

    $response = $this->postJson(route('api.parts.restore', $part->id));

    $response->assertStatus(404);
});