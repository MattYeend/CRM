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
        'parts.access.any',
    ];

    // Create permissions in DB
    $permissionModels = collect($permissions)
        ->map(fn ($name) => Permission::firstOrCreate(['name' => $name]));

    // Create admin role
    $role = Role::factory()->create(['name' => 'admin']);

    $role->permissions()->sync($permissionModels->pluck('id'));

    // Assign role to user
    $this->auth->update([
        'role_id' => $role->id
    ]);

    // Authenticate user
    $this->actingAs($this->auth, 'sanctum');

    // Disable rate limiting
    $this->withoutMiddleware(ThrottleRequests::class);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Index ---------------------------
 * -------------------------------------------------------------
 */
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
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.parts.index'));

    $response->assertStatus(403);
});

/**
 * --------------------------------------------------------------
 * ---------------------------- Show ----------------------------
 * --------------------------------------------------------------
 */
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

test('show returns 403 when user lacks permission', function () {
    $part = Part::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.parts.show', $part->id));

    $response->assertStatus(403);
});

test('show returns 404 for non-existent part', function () {
    $response = $this->getJson(route('api.parts.show', 999999));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Store ---------------------------
 * -------------------------------------------------------------
 */
test('store creates a new part and returns 201', function () {
    $product = Product::factory()->create();

    $payload = [
        'product_id'  => $product->id,
        'sku' => 'SKU-TEST-001',
        'name' => 'Test Part',
        'description' => 'A part created during test',
        'price' => 49.99,
        'currency' => 'GBP',
        'quantity' => 10,
        'type' => 'spare_part',
        'status' => 'active',
        'unit_of_measure' => 'each',
    ];

    $response = $this->postJson(route('api.parts.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['name' => 'Test Part', 'sku' => 'SKU-TEST-001']);
    $this->assertDatabaseHas('parts', ['name' => 'Test Part', 'sku' => 'SKU-TEST-001']);
});

test('store returns 403 when user lacks permission', function () {
    $part = Part::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.parts.store', $part->id));

    $response->assertStatus(403);
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
        'product_id' => $product->id,
        'sku' => 'SKU-DUPE-001',
        'name' => 'Duplicate Part',
        'description' => 'Should fail',
        'price' => 10.00,
    ];

    $response = $this->postJson(route('api.parts.store'), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('sku');
});

/**
 * ------------------------------------------------------------
 * -------------------------- Update --------------------------
 * ------------------------------------------------------------
 */
test('update modifies an existing part', function () {
    $part = Part::factory()->create([
        'name'  => 'Old Part Name',
        'price' => 10.00,
    ]);

    $payload = [
        'name' => 'Updated Part Name',
        'price' => '29.99',
        'quantity' => 5,
    ];

    $response = $this->putJson(route('api.parts.update', $part), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['name' => 'Updated Part Name', 'price' => '29.99']);
    $this->assertDatabaseHas('parts', ['id' => $part->id, 'name' => 'Updated Part Name']);
});

test('update returns 403 when user lacks permission', function () {
    $part = Part::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.parts.update', $part->id));

    $response->assertStatus(403);
});

test('update returns 404 for non-existent part', function () {
    $response = $this->putJson(route('api.parts.update', 999999), ['name' => 'Ghost']);

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Destroy --------------------------
 * -------------------------------------------------------------
 */
test('destroy soft deletes a part and returns 204', function () {
    $part = Part::factory()->create();

    $response = $this->deleteJson(route('api.parts.destroy', $part));

    $response->assertStatus(204);
    $this->assertSoftDeleted('parts', ['id' => $part->id]);
});

test('destroy returns 403 when user lacks permission', function () {
    $part = Part::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.parts.destroy', $part->id));

    $response->assertStatus(403);
});

test('destroy returns 404 for non-existent part', function () {
    $response = $this->deleteJson(route('api.parts.destroy', 999999));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Restore --------------------------
 * -------------------------------------------------------------
 */
test('restore recovers a soft deleted part', function () {
    $part = Part::factory()->create(['created_by' => $this->auth->id]);
    $part->delete();

    $this->assertSoftDeleted('parts', ['id' => $part->id]);

    $response = $this->postJson(route('api.parts.restore', $part->id));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $part->id]);
    $this->assertDatabaseHas('parts', ['id' => $part->id, 'deleted_at' => null]);
});

test('restore returns 403 when user lacks permission', function () {
    $part = Part::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.parts.restore', $part->id));

    $response->assertStatus(403);
});

test('restore returns 404 for non-existent part', function () {
    $response = $this->postJson(route('api.parts.restore', 999999));

    $response->assertStatus(404);
});

test('restore returns 404 when part is not deleted', function () {
    $part = Part::factory()->create();

    $response = $this->postJson(route('api.parts.restore', $part->id));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Stock ---------------------------
 * -------------------------------------------------------------
 */
test('stock returns paginated parts with stock fields', function () {
    Part::factory()->count(5)->create();

    $response = $this->getJson(route('api.parts.stock'));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'name',
                'sku',
                'quantity',
                'reorder_point',
            ],
        ],
        'current_page',
        'per_page',
        'total',
    ]);
});

test('stock returns parts ordered by quantity ascending', function () {
    Part::factory()->create(['name' => 'High Stock Part', 'quantity' => 100]);
    Part::factory()->create(['name' => 'Low Stock Part', 'quantity' => 2]);
    Part::factory()->create(['name' => 'Mid Stock Part', 'quantity' => 50]);

    $response = $this->getJson(route('api.parts.stock'));

    $response->assertStatus(200);

    $quantities = collect($response->json('data'))->pluck('quantity')->values()->all();

    expect($quantities)->toBe(array_values(sort($quantities) ? $quantities : $quantities));
    expect($quantities[0])->toBeLessThanOrEqual($quantities[1]);
    expect($quantities[1])->toBeLessThanOrEqual($quantities[2]);
});

test('stock does not expose fields beyond stock management fields', function () {
    Part::factory()->create();

    $response = $this->getJson(route('api.parts.stock'));

    $response->assertStatus(200);

    $part = $response->json('data.0');

    expect($part)->not->toHaveKey('price');
    expect($part)->not->toHaveKey('cost_price');
    expect($part)->not->toHaveKey('description');
});

test('stock returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update(['role_id' => $role->id]);
    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.parts.stock'));

    $response->assertStatus(403);
});

test('stock returns empty data when no parts exist', function () {
    $response = $this->getJson(route('api.parts.stock'));

    $response->assertStatus(200);
    $response->assertJsonPath('total', 0);
    $this->assertCount(0, $response->json('data'));
});

test('stock paginates at 25 per page', function () {
    Part::factory()->count(30)->create();

    $response = $this->getJson(route('api.parts.stock'));

    $response->assertStatus(200);
    $this->assertCount(25, $response->json('data'));
    $response->assertJsonPath('per_page', 25);
    $response->assertJsonPath('total', 30);
});

/**
 * -------------------------------------------------------------
 * ------------------------- Low Stock -------------------------
 * -------------------------------------------------------------
 */
test('low stock returns only parts at or below reorder point', function () {
    Part::factory()->create([
        'name'          => 'Critical Part',
        'quantity'      => 2,
        'reorder_point' => 5,
    ]);
    Part::factory()->create([
        'name'          => 'At Reorder Part',
        'quantity'      => 5,
        'reorder_point' => 5,
    ]);
    Part::factory()->create([
        'name'          => 'Healthy Part',
        'quantity'      => 100,
        'reorder_point' => 10,
    ]);

    $response = $this->getJson(route('api.parts.stock.low'));

    $response->assertStatus(200);
    $response->assertJsonPath('total', 2);

    $names = collect($response->json('data'))->pluck('name')->all();
    expect($names)->toContain('Critical Part');
    expect($names)->toContain('At Reorder Part');
    expect($names)->not->toContain('Healthy Part');
});

test('low stock returns parts ordered by quantity ascending', function () {
    Part::factory()->create(['quantity' => 4, 'reorder_point' => 10]);
    Part::factory()->create(['quantity' => 1, 'reorder_point' => 10]);
    Part::factory()->create(['quantity' => 7, 'reorder_point' => 10]);

    $response = $this->getJson(route('api.parts.stock.low'));

    $response->assertStatus(200);

    $quantities = collect($response->json('data'))->pluck('quantity')->values()->all();

    expect($quantities[0])->toBeLessThanOrEqual($quantities[1]);
    expect($quantities[1])->toBeLessThanOrEqual($quantities[2]);
});

test('low stock excludes parts with no reorder point set', function () {
    Part::factory()->create([
        'name'          => 'No Reorder Part',
        'quantity'      => 1,
        'reorder_point' => null,
    ]);
    Part::factory()->create([
        'name'          => 'Low Reorder Part',
        'quantity'      => 1,
        'reorder_point' => 5,
    ]);

    $response = $this->getJson(route('api.parts.stock.low'));

    $response->assertStatus(200);
    $response->assertJsonPath('total', 1);

    $names = collect($response->json('data'))->pluck('name')->all();
    expect($names)->toContain('Low Reorder Part');
    expect($names)->not->toContain('No Reorder Part');
});

test('low stock returns empty data when all parts are sufficiently stocked', function () {
    Part::factory()->create(['quantity' => 100, 'reorder_point' => 10]);
    Part::factory()->create(['quantity' => 50,  'reorder_point' => 5]);

    $response = $this->getJson(route('api.parts.stock.low'));

    $response->assertStatus(200);
    $response->assertJsonPath('total', 0);
    $this->assertCount(0, $response->json('data'));
});

test('low stock returns correct structure', function () {
    Part::factory()->create(['quantity' => 1, 'reorder_point' => 10]);

    $response = $this->getJson(route('api.parts.stock.low'));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'name',
                'sku',
                'quantity',
                'reorder_point',
            ],
        ],
        'current_page',
        'per_page',
        'total',
    ]);
});

test('low stock returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update(['role_id' => $role->id]);
    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.parts.stock.low'));

    $response->assertStatus(403);
});

test('low stock paginates at 25 per page', function () {
    Part::factory()->count(30)->create(['quantity' => 1, 'reorder_point' => 10]);

    $response = $this->getJson(route('api.parts.stock.low'));

    $response->assertStatus(200);
    $this->assertCount(25, $response->json('data'));
    $response->assertJsonPath('per_page', 25);
    $response->assertJsonPath('total', 30);
});
