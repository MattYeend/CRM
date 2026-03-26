<?php

use App\Models\Deal;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'products.view.all',
        'products.create',
        'products.update.any',
        'products.delete.any',
        'products.restore.any',
        'deals.products.add',
        'deals.products.update',
        'deals.products.remove',
        'deals.products.restore',
        'quotes.products.add',
        'quotes.products.update',
        'quotes.products.remove',
        'quotes.products.restore',
        'orders.products.add',
        'orders.products.update',
        'orders.products.remove',
        'orders.products.restore',
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
test('index returns paginated products', function () {
    // Ensure factory creates valid products
    Product::factory()->count(12)->create();

    $response = $this->getJson(route('api.products.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);

    // Should return 5 items
    $this->assertCount(5, $response->json('data'));
});

/**
 * --------------------------------------------------------------
 * ---------------------------- Show ----------------------------
 * --------------------------------------------------------------
 */
test('show returns a single product', function () {
    $product = Product::factory()->create();

    $response = $this->getJson(route('api.products.show', $product));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $product->id, 'name' => $product->name]);
    $response->assertJsonStructure([
        'id',
        'sku',
        'name',
        'description',
        'price',
        'currency',
        'quantity',
        'meta',
    ]);
});

test('store creates a new product and returns 201', function () {
    $payload = [
        'sku' => 'SKU-1234',
        'name' => 'Test Product',
        'description' => 'A product created during test',
        'price' => 99.99,
        'currency' => 'USD',
        'quantity' => 10,
        'meta' => ['color' => 'red'],
    ];

    $response = $this->postJson(route('api.products.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['name' => 'Test Product', 'sku' => 'SKU-1234']);
    $this->assertDatabaseHas('products', ['name' => 'Test Product', 'sku' => 'SKU-1234']);
});

test('store returns validation error when required fields missing', function () {
    // missing 'name'
    $payload = [
        'sku' => 'SKU-9999',
    ];

    $response = $this->postJson(route('api.products.store'), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('name');
});

/**
 * --------------------------------------------------------------
 * --------------------------- Update ---------------------------
 * --------------------------------------------------------------
 */
test('update modifies an existing product', function () {
    $product = Product::factory()->create([
        'name' => 'Old Product',
        'price' => 10,
    ]);

    $payload = [
        'name' => 'Updated Product',
        'price' => 25.5,
        'quantity' => 7,
    ];

    $response = $this->putJson(route('api.products.update', $product), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['name' => 'Updated Product', 'price' => 25.5]);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Updated Product']);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Destroy --------------------------
 * -------------------------------------------------------------
 */
test('destroy deletes the product', function () {
    $product = Product::factory()->create();

    $response = $this->deleteJson(route('api.products.destroy', $product));

    $response->assertStatus(204);

    $this->assertSoftDeleted('products', ['id' => $product->id]);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Restore --------------------------
 * -------------------------------------------------------------
 */
test('restore deleted product', function () {
    $product = Product::factory()->create(['created_by' => $this->auth->id]);

    // Soft delete the model
    $product->delete();

    // Ensure it is soft deleted
    $this->assertSoftDeleted('products', ['id' => $product->id]);

    // Call restore route
    $response = $this->postJson(route('api.products.restore', $product->id));

    $response->assertStatus(200);

    // Assert JSON response includes restored model
    $response->assertJsonFragment([
        'id' => $product->id,
    ]);

    // Assert database no longer has deleted_at
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'deleted_at' => null,
    ]);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Orders ---------------------------
 * -------------------------------------------------------------
 */
test('add orders to a product', function () {
    $product = Product::factory()->create();
    $order = Order::factory()->create();

    $payload = [
        'orders' => [
            [
                'order_id' => $order->id,
                'quantity' => 3,
                'price' => 50.25,
                'meta' => ['size' => 'L']
            ]
        ]
    ];

    $response = $this->postJson(route('api.products.orders.add', $product), $payload);
    $response->assertStatus(200);
    $response->assertJsonFragment(['message' => 'Orders added to product']);

    $this->assertDatabaseHas('order_products', [
        'product_id' => $product->id,
        'order_id' => $order->id,
        'quantity' => 3,
        'price' => 50.25,
    ]);
});

test('update orders on a product', function () {
    $product = Product::factory()->create();
    $order = Order::factory()->create();
    $product->orders()->attach($order->id, ['quantity' => 1, 'price' => 20]);

    $payload = [
        'orders' => [
            [
                'order_id' => $order->id,
                'quantity' => 5,
                'price' => 100,
            ]
        ]
    ];

    $response = $this->putJson(route('api.products.orders.update', $product), $payload);
    $response->assertStatus(200);

    $this->assertDatabaseHas('order_products', [
        'product_id' => $product->id,
        'order_id' => $order->id,
        'quantity' => 5,
        'price' => 100,
    ]);
});

test('remove an order from a product', function () {
    $product = Product::factory()->create();
    $order = Order::factory()->create();
    $product->orders()->attach($order->id, ['quantity' => 1, 'price' => 20]);

    $response = $this->deleteJson(route('api.products.orders.remove', [$product, $order]));
    $response->assertStatus(200);

    $deletedAt = DB::table('order_products')
        ->where('product_id', $product->id)
        ->where('order_id', $order->id)
        ->value('deleted_at');

    $this->assertNotNull($deletedAt, 'Pivot row should be soft-deleted.');
});

test('restore a previously removed order on a product', function () {
    $product = Product::factory()->create();
    $order = Order::factory()->create();
    $product->orders()->attach($order->id, ['quantity' => 1, 'price' => 20, 'deleted_at' => now()]);

    $response = $this->postJson(route('api.products.orders.restore', [$product, $order]));
    $response->assertStatus(200);

    $this->assertDatabaseHas('order_products', [
        'product_id' => $product->id,
        'order_id' => $order->id,
        'deleted_at' => null,
    ]);
});

/**
 * --------------------------------------------------------------
 * --------------------------- Quotes ---------------------------
 * --------------------------------------------------------------
 */
test('add quotes to a product', function () {
    $product = Product::factory()->create();
    $quote = Quote::factory()->create();

    $payload = [
        'quotes' => [
            ['quote_id' => $quote->id, 'quantity' => 2, 'price' => 75.50]
        ]
    ];

    $response = $this->postJson(route('api.products.quotes.add', $product), $payload);
    $response->assertStatus(200);
    $response->assertJsonFragment(['message' => 'Quotes added to product']);

    $this->assertDatabaseHas('quote_products', [
        'product_id' => $product->id,
        'quote_id' => $quote->id,
        'quantity' => 2,
        'price' => 75.50,
    ]);
});

test('update quotes on a product', function () {
    $product = Product::factory()->create();
    $quote = Quote::factory()->create();
    $product->quotes()->attach($quote->id, ['quantity' => 1, 'price' => 20]);

    $payload = [
        'quotes' => [
            ['quote_id' => $quote->id, 'quantity' => 4, 'price' => 80]
        ]
    ];

    $response = $this->putJson(route('api.products.quotes.update', $product), $payload);
    $response->assertStatus(200);

    $this->assertDatabaseHas('quote_products', [
        'product_id' => $product->id,
        'quote_id' => $quote->id,
        'quantity' => 4,
        'price' => 80,
    ]);
});

test('remove a quote from a product', function () {
    $product = Product::factory()->create();
    $quote = Quote::factory()->create();
    $product->quotes()->attach($quote->id, ['quantity' => 1, 'price' => 20]);

    $response = $this->deleteJson(route('api.products.quotes.remove', [$product, $quote]));
    $response->assertStatus(200);

    $deletedAt = DB::table('quote_products')
        ->where('product_id', $product->id)
        ->where('quote_id', $quote->id)
        ->value('deleted_at');

    $this->assertNotNull($deletedAt, 'Pivot row should be soft-deleted.');
});

test('restore a previously removed quote on a product', function () {
    $product = Product::factory()->create();
    $quote = Quote::factory()->create();
    $product->quotes()->attach($quote->id, ['quantity' => 1, 'price' => 20, 'deleted_at' => now()]);

    $response = $this->postJson(route('api.products.quotes.restore', [$product, $quote]));
    $response->assertStatus(200);

    $this->assertDatabaseHas('quote_products', [
        'product_id' => $product->id,
        'quote_id' => $quote->id,
        'deleted_at' => null,
    ]);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Deals ---------------------------
 * -------------------------------------------------------------
 */
test('add deals to a product', function () {
    $product = Product::factory()->create();
    $deal = Deal::factory()->create();

    $payload = [
        'deals' => [
            ['deal_id' => $deal->id, 'quantity' => 3, 'price' => 60]
        ]
    ];

    $response = $this->postJson(route('api.products.deals.add', $product), $payload);
    $response->assertStatus(200);
    $response->assertJsonFragment(['message' => 'Deals added to product']);

    $this->assertDatabaseHas('deal_products', [
        'product_id' => $product->id,
        'deal_id' => $deal->id,
        'quantity' => 3,
        'price' => 60,
    ]);
});

test('update deals on a product', function () {
    $product = Product::factory()->create();
    $deal = Deal::factory()->create();
    $product->deals()->attach($deal->id, ['quantity' => 1, 'price' => 20]);

    $payload = [
        'deals' => [
            ['deal_id' => $deal->id, 'quantity' => 5, 'price' => 100]
        ]
    ];

    $response = $this->putJson(route('api.products.deals.update', $product), $payload);
    $response->assertStatus(200);

    $this->assertDatabaseHas('deal_products', [
        'product_id' => $product->id,
        'deal_id' => $deal->id,
        'quantity' => 5,
        'price' => 100,
    ]);
});

test('remove a deal from a product', function () {
    $product = Product::factory()->create();
    $deal = Deal::factory()->create();
    $product->deals()->attach($deal->id, ['quantity' => 1, 'price' => 20]);

    $response = $this->deleteJson(route('api.products.deals.remove', [$product, $deal]));
    $response->assertStatus(200);

    $deletedAt = DB::table('deal_products')
        ->where('product_id', $product->id)
        ->where('deal_id', $deal->id)
        ->value('deleted_at');

    $this->assertNotNull($deletedAt, 'Pivot row should be soft-deleted.');
});

test('restore a previously removed deal on a product', function () {
    $product = Product::factory()->create();
    $deal = Deal::factory()->create();
    $product->deals()->attach($deal->id, ['quantity' => 1, 'price' => 20, 'deleted_at' => now()]);

    $response = $this->postJson(route('api.products.deals.restore', [$product, $deal]));
    $response->assertStatus(200);

    $this->assertDatabaseHas('deal_products', [
        'product_id' => $product->id,
        'deal_id' => $deal->id,
        'deleted_at' => null,
    ]);
});