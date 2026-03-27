<?php

use App\Models\Deal;
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
        'quotes.view.all',
        'quotes.create',
        'quotes.update.any',
        'quotes.delete.any',
        'quotes.restore.any',
        'quotes.products.add',
        'quotes.products.update',
        'quotes.products.remove',
        'quotes.products.restore',
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
test('index returns paginated quotes with relations', function () {
    $deal = Deal::factory()->create();

    Quote::factory()->count(12)->create([
        'deal_id' => $deal->id,
        'created_by' => $this->auth->id,
    ]);

    $response = $this->getJson(route('api.quotes.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);

    $this->assertCount(5, $response->json('data'));

    $first = $response->json('data')[0];

    $this->assertArrayHasKey('deal', $first);
    $this->assertArrayHasKey('creator', $first);
});

test('index returns all quotes when no pagination specified', function () {
    Quote::factory()->count(3)->create();

    $response = $this->getJson(route('api.quotes.index'));

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

    $response = $this->getJson(route('api.quotes.index'));

    $response->assertStatus(403);
});

/**
 * --------------------------------------------------------------
 * ---------------------------- Show ----------------------------
 * --------------------------------------------------------------
 */
test('show returns a quote with deal and creator loaded', function () {
    $deal = Deal::factory()->create();

    $quote = Quote::factory()->create([
        'deal_id' => $deal->id,
        'created_by' => $this->auth->id,
    ]);

    $response = $this->getJson(route('api.quotes.show', $quote));

    $response->assertStatus(200);

    $response->assertJsonFragment([
        'id' => $quote->id,
    ]);

    $response->assertJsonStructure([
        'id',
        'deal_id',
        'currency',
        'subtotal',
        'tax',
        'total',
        'sent_at',
        'accepted_at',
        'deal' => [],
        'creator' => [],
    ]);
});

test('show returns 403 when user lacks permission', function () {
    $quote = Quote::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.quotes.show', $quote->id));

    $response->assertStatus(403);
});

test('show returns 404 for non-existent quote', function () {
    $response = $this->getJson(route('api.quotes.show', 999999));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Store ---------------------------
 * -------------------------------------------------------------
 */
test('store creates a quote and returns 201', function () {
    $deal = Deal::factory()->create();

    $payload = [
        'deal_id' => $deal->id,
        'currency' => 'GBP',
        'subtotal' => 100,
        'tax' => 20,
        'total' => 120,
    ];

    $response = $this->postJson(route('api.quotes.store'), $payload);

    $response->assertStatus(201);

    $response->assertJsonFragment([
        'currency' => 'GBP',
        'subtotal' => 100,
        'total' => 120,
    ]);

    $this->assertDatabaseHas('quotes', [
        'deal_id' => $deal->id,
        'currency' => 'GBP',
    ]);
});

test('store returns 403 when user lacks permission', function () {
    $quote = Quote::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.quotes.store', $quote->id));

    $response->assertStatus(403);
});

/**
 * --------------------------------------------------------------
 * --------------------------- Update ---------------------------
 * --------------------------------------------------------------
 */
test('update modifies an existing quote', function () {
    $deal = Deal::factory()->create();

    $quote = Quote::factory()->create([
        'deal_id' => $deal->id,
        'currency' => 'GBP',
        'subtotal' => 100,
        'tax' => 20,
        'total' => 120,
    ]);

    $payload = [
        'currency' => 'USD',
        'subtotal' => 200,
        'tax' => 40,
        'total' => 240,
    ];

    $response = $this->putJson(route('api.quotes.update', $quote), $payload);

    $response->assertStatus(200);

    $response->assertJsonFragment([
        'currency' => 'USD',
        'total' => 240,
    ]);

    $this->assertDatabaseHas('quotes', [
        'id' => $quote->id,
        'currency' => 'USD',
    ]);
});

test('update returns 403 when user lacks permission', function () {
    $quote = Quote::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.quotes.update', $quote->id));

    $response->assertStatus(403);
});

test('update returns 404 for non-existent quote', function () {
    $response = $this->putJson(route('api.quotes.update', 999999), ['name' => 'Ghost']);

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Destroy --------------------------
 * -------------------------------------------------------------
 */
test('destroy deletes the quote', function () {
    $deal = Deal::factory()->create();

    $quote = Quote::factory()->create([
        'deal_id' => $deal->id,
    ]);

    $response = $this->deleteJson(route('api.quotes.destroy', $quote));

    $response->assertStatus(204);

    $this->assertSoftDeleted('quotes', [
        'id' => $quote->id,
    ]);
});

test('destroy returns 403 when user lacks permission', function () {
    $quote = Quote::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.quotes.destroy', $quote->id));

    $response->assertStatus(403);
});

test('destroy returns 404 for non-existent quote', function () {
    $response = $this->deleteJson(route('api.quotes.destroy', 999999));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Restore --------------------------
 * -------------------------------------------------------------
 */
test('restore deleted quotes', function () {
    $deal = Deal::factory()->create();

    $quote = Quote::factory()->create([
        'deal_id' => $deal->id,
        'created_by' => $this->auth->id,
    ]);

    $quote->delete();

    $this->assertSoftDeleted('quotes', [
        'id' => $quote->id,
    ]);

    $response = $this->postJson(route('api.quotes.restore', $quote->id));

    $response->assertStatus(200);

    $response->assertJsonFragment([
        'id' => $quote->id,
    ]);

    $this->assertDatabaseHas('quotes', [
        'id' => $quote->id,
        'deleted_at' => null,
    ]);
});

test('restore returns 403 when user lacks permission', function () {
    $quote = Quote::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.quotes.restore', $quote->id));

    $response->assertStatus(403);
});

test('restore returns 404 for non-existent quote', function () {
    $response = $this->postJson(route('api.quotes.restore', 999999));

    $response->assertStatus(404);
});

test('restore returns 404 when quote is not deleted', function () {
    $quote = Quote::factory()->create();

    $response = $this->postJson(route('api.quotes.restore', $quote->id));

    $response->assertStatus(404);
});

/**
 * --------------------------------------------------------------
 * -------------------------- Products --------------------------
 * --------------------------------------------------------------
 */
test('add products to a quote', function () {
    $quote = Quote::factory()->create();
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

    $response = $this->postJson(route('api.quotes.products.add', $quote), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'message' => 'Products added to quote'
    ]);

    $this->assertDatabaseHas('quote_products', [
        'quote_id' => $quote->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 25.50,
    ]);
});

test('update products on a quote', function () {
    $quote = Quote::factory()->create();
    $product = Product::factory()->create();

    // Attach first
    $quote->products()->attach($product->id, ['quantity' => 1, 'price' => 10]);

    $payload = [
        'products' => [
            [
                'product_id' => $product->id,
                'quantity' => 5,
                'price' => 50,
            ]
        ]
    ];

    $response = $this->putJson(route('api.quotes.products.update', $quote), $payload);

    $response->assertStatus(200);

    $this->assertDatabaseHas('quote_products', [
        'quote_id' => $quote->id,
        'product_id' => $product->id,
        'quantity' => 5,
        'price' => 50,
    ]);
});

test('remove a product from a quote', function () {
    $quote = Quote::factory()->create();
    $product = Product::factory()->create();

    // Attach first
    $quote->products()->attach($product->id, ['quantity' => 1, 'price' => 10]);

    $response = $this->deleteJson(route('api.quotes.products.remove', [$quote, $product]));

    $response->assertStatus(200);

    // Assert the row is soft-deleted
    $this->assertDatabaseHas('quote_products', [
        'quote_id' => $quote->id,
        'product_id' => $product->id,
    ]);

    $deletedAt = DB::table('quote_products')
        ->where('quote_id', $quote->id)
        ->where('product_id', $product->id)
        ->value('deleted_at');

    $this->assertNotNull($deletedAt, 'Product row should be soft-deleted.');
});

test('restore a previously removed product on a quote', function () {
    $quote = Quote::factory()->create();
    $product = Product::factory()->create();

    // Attach and then detach (soft delete if you implement it)
    $quote->products()->attach($product->id, ['quantity' => 1, 'price' => 10]);
    $quote->products()->detach($product->id);

    // Call restore route
    $response = $this->postJson(route('api.quotes.products.restore', [$quote, $product]));

    $response->assertStatus(200);

    $this->assertDatabaseHas('quote_products', [
        'quote_id' => $quote->id,
        'product_id' => $product->id,
    ]);
});
