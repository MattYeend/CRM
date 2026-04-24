<?php

use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductStockMovement;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'productStockMovements.view.all',
        'productStockMovements.create',
        'productStockMovements.access.any',
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
test('index returns paginated product stock movements', function () {
    $product = Product::factory()->create();
    ProductStockMovement::factory()->count(12)->create(['product_id' => $product->id]);

    $response = $this->getJson(route('api.productStockMovements.index', $product) . '?per_page=5');

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('index returns all product stock movements when no pagination specified', function () {
    $product = Product::factory()->create();
    ProductStockMovement::factory()->count(3)->create(['product_id' => $product->id]);

    $response = $this->getJson(route('api.productStockMovements.index', $product));

    $response->assertStatus(200);
    $this->assertCount(3, $response->json('data'));
});

test('index returns 403 when user lacks permission', function () {
    $product = Product::factory()->create();
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update(['role_id' => $role->id]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.productStockMovements.index', $product));

    $response->assertStatus(403);
});

/**
 * --------------------------------------------------------------
 * ---------------------------- Show ----------------------------
 * --------------------------------------------------------------
 */
test('show returns a single product stock movement', function () {
    $product = Product::factory()->create();
    $productStockMovement = ProductStockMovement::factory()->create(['product_id' => $product->id]);

    $response = $this->getJson(route('api.productStockMovements.show', [$product, $productStockMovement]));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $productStockMovement->id]);
    $response->assertJsonStructure(['id', 'product_id', 'type', 'quantity', 'quantity_before', 'quantity_after', 'created_by']);
});

test('show returns 403 when user lacks permission', function () {
    $product = Product::factory()->create();
    $productStockMovement = ProductStockMovement::factory()->create(['product_id' => $product->id]);

    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update(['role_id' => $role->id]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.productStockMovements.show', [$product, $productStockMovement]));

    $response->assertStatus(403);
});

test('show returns 404 for non-existent product stock movement', function () {
    $product = Product::factory()->create();

    $response = $this->getJson(route('api.productStockMovements.show', [$product, 999999]));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Store ---------------------------
 * -------------------------------------------------------------
 */
test('store creates a new product stock movement and returns 201', function () {
    $product = Product::factory()->create(['quantity' => 10]);

    $payload = [
        'type' => 'in',
        'quantity' => 5,
        'notes' => 'Restocking',
    ];

    $response = $this->postJson(route('api.productStockMovements.store', $product), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['type' => 'in', 'quantity' => 5]);
    $this->assertDatabaseHas('product_stock_movements', [
        'product_id' => $product->id,
        'type' => 'in',
        'quantity' => 5,
        'quantity_before' => 10,
        'quantity_after' => 15,
    ]);
});

test('store updates the product quantity after movement', function () {
    $product = Product::factory()->create(['quantity' => 20]);

    $payload = [
        'type' => 'out',
        'quantity' => 8,
    ];

    $this->postJson(route('api.productStockMovements.store', $product), $payload);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'quantity' => 12,
    ]);
});

test('store returns 422 when stock would go below zero', function () {
    $product = Product::factory()->create(['quantity' => 5]);

    $payload = [
        'type' => 'out',
        'quantity' => 10,
    ];

    $response = $this->postJson(route('api.productStockMovements.store', $product), $payload);

    $response->assertStatus(422);
    $response->assertJsonFragment(['message' => "Insufficient stock. Current: 5, requested: -10."]);
});

test('store does not update product quantity when stock is insufficient', function () {
    $product = Product::factory()->create(['quantity' => 5]);

    $payload = [
        'type' => 'out',
        'quantity' => 10,
    ];

    $this->postJson(route('api.productStockMovements.store', $product), $payload);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'quantity' => 5,
    ]);
});

test('store returns 403 when user lacks permission', function () {
    $product = Product::factory()->create();
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update(['role_id' => $role->id]);

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.productStockMovements.store', $product), [
        'type' => 'in',
        'quantity' => 5,
    ]);

    $response->assertStatus(403);
});

test('store returns 422 when required fields are missing', function () {
    $product = Product::factory()->create();

    $response = $this->postJson(route('api.productStockMovements.store', $product), []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['type', 'quantity']);
});

test('store returns 422 when type is invalid', function () {
    $product = Product::factory()->create(['quantity' => 10]);

    $payload = [
        'type' => 'invalid_type',
        'quantity' => 5,
    ];

    $response = $this->postJson(route('api.productStockMovements.store', $product), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('type');
});

test('store returns 422 when quantity is zero', function () {
    $product = Product::factory()->create(['quantity' => 10]);

    $payload = [
        'type' => 'in',
        'quantity' => 0,
    ];

    $response = $this->postJson(route('api.productStockMovements.store', $product), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('quantity');
});

test('store returns 404 for non-existent product', function () {
    $response = $this->postJson(route('api.productStockMovements.store', 999999), [
        'type' => 'in',
        'quantity' => 5,
    ]);

    $response->assertStatus(404);
});
