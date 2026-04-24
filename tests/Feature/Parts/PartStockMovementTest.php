<?php

use App\Models\Part;
use App\Models\PartStockMovement;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'partStockMovements.view.all',
        'partStockMovements.create',
        'partStockMovements.access.any',
    ];

    $permissionModels = collect($permissions)
        ->map(fn($name) => Permission::firstOrCreate(['name' => $name]));

    $role = Role::factory()->create(['name' => 'admin']);
    $role->permissions()->sync($permissionModels->pluck('id'));

    $this->auth->update(['role_id' => $role->id]);

    $this->actingAs($this->auth, 'sanctum');

    $this->withoutMiddleware(ThrottleRequests::class);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Index ---------------------------
 * -------------------------------------------------------------
 */
test('index returns paginated part stock movements', function () {
    $part = Part::factory()->create();
    PartStockMovement::factory()->count(12)->create(['part_id' => $part->id]);

    $response = $this->getJson(route('api.partStockMovements.index', $part) . '?per_page=5');

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('index returns all part stock movements when no pagination specified', function () {
    $part = Part::factory()->create();
    PartStockMovement::factory()->count(3)->create(['part_id' => $part->id]);

    $response = $this->getJson(route('api.partStockMovements.index', $part));

    $response->assertStatus(200);
    $this->assertCount(3, $response->json('data'));
});

test('index returns 403 when user lacks permission', function () {
    $part = Part::factory()->create();
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update(['role_id' => $role->id]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.partStockMovements.index', $part));

    $response->assertStatus(403);
});

/**
 * --------------------------------------------------------------
 * ---------------------------- Show ----------------------------
 * --------------------------------------------------------------
 */
test('show returns a single part stock movement', function () {
    $part     = Part::factory()->create();
    $partStockMovement = PartStockMovement::factory()->create(['part_id' => $part->id]);

    $response = $this->getJson(route('api.partStockMovements.show', [$part, $partStockMovement]));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $partStockMovement->id]);
    $response->assertJsonStructure(['id', 'part_id', 'type', 'quantity', 'quantity_before', 'quantity_after', 'created_by']);
});

test('show returns 403 when user lacks permission', function () {
    $part = Part::factory()->create();
    $partStockMovement = PartStockMovement::factory()->create(['part_id' => $part->id]);

    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update(['role_id' => $role->id]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.partStockMovements.show', [$part, $partStockMovement]));

    $response->assertStatus(403);
});

test('show returns 404 for non-existent part stock movement', function () {
    $part = Part::factory()->create();

    $response = $this->getJson(route('api.partStockMovements.show', [$part, 999999]));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Store ---------------------------
 * -------------------------------------------------------------
 */
test('store creates a new part stock movement and returns 201', function () {
    $part = Part::factory()->create(['quantity' => 10]);

    $payload = [
        'type' => 'in',
        'quantity' => 5,
        'notes' => 'Restocking',
    ];

    $response = $this->postJson(route('api.partStockMovements.store', $part), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['type' => 'in', 'quantity' => 5]);
    $this->assertDatabaseHas('part_stock_movements', [
        'part_id' => $part->id,
        'type' => 'in',
        'quantity' => 5,
        'quantity_before' => 10,
        'quantity_after' => 15,
    ]);
});

test('store updates the part quantity after movement', function () {
    $part = Part::factory()->create(['quantity' => 20]);

    $payload = [
        'type' => 'out',
        'quantity' => 8,
    ];

    $this->postJson(route('api.partStockMovements.store', $part), $payload);

    $this->assertDatabaseHas('parts', [
        'id' => $part->id,
        'quantity' => 12,
    ]);
});

test('store returns 422 when stock would go below zero', function () {
    $part = Part::factory()->create(['quantity' => 5]);

    $payload = [
        'type' => 'out',
        'quantity' => 10,
    ];

    $response = $this->postJson(route('api.partStockMovements.store', $part), $payload);

    $response->assertStatus(422);
    $response->assertJsonFragment(['message' => "Insufficient stock. Current: 5, requested: -10."]);
});

test('store does not update part quantity when stock is insufficient', function () {
    $part = Part::factory()->create(['quantity' => 5]);

    $payload = [
        'type' => 'out',
        'quantity' => 10,
    ];

    $this->postJson(route('api.partStockMovements.store', $part), $payload);

    $this->assertDatabaseHas('parts', [
        'id' => $part->id,
        'quantity' => 5,
    ]);
});

test('store returns 403 when user lacks permission', function () {
    $part = Part::factory()->create();
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update(['role_id' => $role->id]);

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.partStockMovements.store', $part), [
        'type' => 'in',
        'quantity' => 5,
    ]);

    $response->assertStatus(403);
});

test('store returns 422 when required fields are missing', function () {
    $part = Part::factory()->create();

    $response = $this->postJson(route('api.partStockMovements.store', $part), []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['type', 'quantity']);
});

test('store returns 422 when type is invalid', function () {
    $part = Part::factory()->create(['quantity' => 10]);

    $payload = [
        'type' => 'invalid_type',
        'quantity' => 5,
    ];

    $response = $this->postJson(route('api.partStockMovements.store', $part), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('type');
});

test('store returns 422 when quantity is zero', function () {
    $part = Part::factory()->create(['quantity' => 10]);

    $payload = [
        'type' => 'in',
        'quantity' => 0,
    ];

    $response = $this->postJson(route('api.partStockMovements.store', $part), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('quantity');
});

test('store returns 404 for non-existent part', function () {
    $response = $this->postJson(route('api.partStockMovements.store', 999999), [
        'type' => 'in',
        'quantity' => 5,
    ]);

    $response->assertStatus(404);
});
