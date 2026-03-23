<?php

use App\Models\Deal;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'orders.view.all',
        'orders.create',
        'orders.update.any',
        'orders.delete.any',
        'orders.restore.any',
        'orders.products.add',
        'orders.products.update',
        'orders.products.remove',
        'orders.products.restore',
    ];

    // Create permissions
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


test('index returns paginated orders', function () {

    Order::factory()->count(12)->create();

    $response = $this->getJson(route('api.orders.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);

    $this->assertCount(5, $response->json('data'));
});


test('show returns a single order', function () {

    $order = Order::factory()->create();

    $response = $this->getJson(route('api.orders.show', $order));

    $response->assertStatus(200);

    $response->assertJsonFragment([
        'id' => $order->id,
        'amount' => $order->amount,
    ]);

    $response->assertJsonStructure([
        'id',
        'user_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'paid_at',
        'payment_intent_id',
        'charge_id',
        'meta',
    ]);
});


test('store creates a new order and returns 201', function () {

    $user = User::factory()->create();
    $deal = Deal::factory()->create();

    $payload = [
        'user_id' => $user->id,
        'deal_id' => $deal->id,
        'amount' => 120.50,
        'currency' => 'USD',
        'status' => 'pending',
        'payment_method' => 'card',
        'meta' => [],
    ];

    $response = $this->postJson(route('api.orders.store'), $payload);

    $response->assertStatus(201);

    $response->assertJsonFragment([
        'amount' => 120.50,
        'currency' => 'USD',
    ]);

    $this->assertDatabaseHas('orders', [
        'deal_id' => $deal->id,
        'amount' => 120.50,
        'currency' => 'USD',
    ]);
});


test('store returns validation error when required fields missing', function () {

    $payload = [
        'currency' => 'USD'
    ];

    $response = $this->postJson(route('api.orders.store'), $payload);

    $response->assertStatus(422);

    $response->assertJsonValidationErrors('amount');
});


test('update modifies an existing order', function () {

    $order = Order::factory()->create([
        'amount' => 50,
        'currency' => 'USD',
    ]);

    $payload = [
        'amount' => 200,
        'status' => 'paid',
    ];

    $response = $this->putJson(route('api.orders.update', $order), $payload);

    $response->assertStatus(200);

    $response->assertJsonFragment([
        'amount' => 200,
        'status' => 'paid',
    ]);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'amount' => 200,
    ]);
});


test('destroy deletes the order', function () {

    $order = Order::factory()->create();

    $response = $this->deleteJson(route('api.orders.destroy', $order));

    $response->assertStatus(204);

    $this->assertSoftDeleted('orders', [
        'id' => $order->id,
    ]);
});


test('restore deleted order', function () {

    $order = Order::factory()->create([
        'created_by' => $this->auth->id,
    ]);

    $order->delete();

    $this->assertSoftDeleted('orders', [
        'id' => $order->id,
    ]);

    $response = $this->postJson(route('api.orders.restore', $order->id));

    $response->assertStatus(200);

    $response->assertJsonFragment([
        'id' => $order->id,
    ]);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'deleted_at' => null,
    ]);
});

test('add products to an order', function () {

    $order = Order::factory()->create();
    $product = Product::factory()->create();

    $payload = [
        'products' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'price' => 25.50,
                'meta' => ['color' => 'red'],
            ]
        ]
    ];

    $response = $this->postJson(route('api.orders.products.add', $order), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'message' => 'Products added to order'
    ]);

    $this->assertDatabaseHas('order_products', [
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 25.50,
    ]);
});

test('update products on an order', function () {

    $order = Order::factory()->create();
    $product = Product::factory()->create();

    // Attach first
    $order->products()->attach($product->id, ['quantity' => 1, 'price' => 10]);

    $payload = [
        'products' => [
            [
                'product_id' => $product->id,
                'quantity' => 5,
                'price' => 50,
            ]
        ]
    ];

    $response = $this->putJson(route('api.orders.products.update', $order), $payload);

    $response->assertStatus(200);

    $this->assertDatabaseHas('order_products', [
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 5,
        'price' => 50,
    ]);
});

test('remove a product from an order', function () {

    $order = Order::factory()->create();
    $product = Product::factory()->create();

    // Attach first
    $order->products()->attach($product->id, ['quantity' => 1, 'price' => 10]);

    $response = $this->deleteJson(route('api.orders.products.remove', [$order, $product]));

    $response->assertStatus(200);

    // Assert the row is soft-deleted
    $this->assertDatabaseHas('order_products', [
        'order_id' => $order->id,
        'product_id' => $product->id,
    ]);

    $deletedAt = DB::table('order_products')
        ->where('order_id', $order->id)
        ->where('product_id', $product->id)
        ->value('deleted_at');

    $this->assertNotNull($deletedAt, 'Product row should be soft-deleted.');
});

test('restore a previously removed product on an order', function () {

    $order = Order::factory()->create();
    $product = Product::factory()->create();

    // Attach and then detach (soft delete if you implement it)
    $order->products()->attach($product->id, ['quantity' => 1, 'price' => 10]);
    $order->products()->detach($product->id);

    // Call restore route
    $response = $this->postJson(route('api.orders.products.restore', [$order, $product]));

    $response->assertStatus(200);

    $this->assertDatabaseHas('order_products', [
        'order_id' => $order->id,
        'product_id' => $product->id,
    ]);
});