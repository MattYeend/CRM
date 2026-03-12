<?php

namespace Tests\Feature\Products;

use App\Models\Deal;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductDeal;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test user
    $this->auth = User::factory()->create();

    // Permissions required for ProductDeals
    $permissions = [
        'productDeals.view.all',
        'productDeals.create',
        'productDeals.update.any',
        'productDeals.delete.any',
        'productDeals.restore.any',
    ];

    // Create Permission models
    $permissionModels = collect($permissions)
        ->map(fn($name) => Permission::firstOrCreate(['name' => $name]));

    // Create admin role and attach permissions
    $role = \App\Models\Role::factory()->create(['name' => 'admin']);
    $role->permissions()->sync($permissionModels->pluck('id'));

    // Attach role to test user
    $this->auth->roles()->sync([$role->id]);
    $this->auth->refresh(); // Refresh user to load permissions

    // Authenticate user
    $this->actingAs($this->auth, 'sanctum');

    // Disable throttling for tests
    $this->withoutMiddleware(ThrottleRequests::class);
});

test('index returns paginated product deals', function () {
    ProductDeal::factory()->count(12)->create(['created_by' => $this->auth->id]);

    $response = $this->getJson(route('product-deals.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('show returns a single product deal', function () {
    $productDeal = ProductDeal::factory()->create(['created_by' => $this->auth->id]);

    $response = $this->getJson(route('product-deals.show', $productDeal));

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'id' => $productDeal->id,
        'product_id' => $productDeal->product_id,
        'deal_id' => $productDeal->deal_id,
    ]);

    $response->assertJsonStructure([
        'id',
        'product_id',
        'deal_id',
        'quantity',
        'unit_price',
        'total_price',
        'currency',
        'meta',
    ]);
});

test('store creates a new product deal and returns 201', function () {
    $product = Product::factory()->create();
    $deal = Deal::factory()->create();

    $payload = [
        'product_id' => $product->id,
        'deal_id' => $deal->id,
        'quantity' => 2,
        'unit_price' => 50,
        'total_price' => 100,
        'currency' => 'USD',
        'meta' => ['note' => 'test line item'],
    ];

    $response = $this->postJson(route('product-deals.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment([
        'product_id' => $product->id,
        'deal_id' => $deal->id,
    ]);

    $this->assertDatabaseHas('product_deals', [
        'product_id' => $product->id,
        'deal_id' => $deal->id,
    ]);
});

test('store returns validation error when required fields missing', function () {
    $response = $this->postJson(route('product-deals.store'), []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['deal_id', 'product_id']);
});

test('update modifies an existing product deal', function () {
    $productDeal = ProductDeal::factory()->create([
        'quantity' => 1,
        'unit_price' => 20,
        'created_by' => $this->auth->id,
    ]);

    $payload = [
        'deal_id' =>$productDeal->deal_id,
        'quantity' => 5,
        'unit_price' => 30,
        'total_price' => 150,
    ];

    $response = $this->putJson(route('product-deals.update', $productDeal), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'quantity' => 5,
        'unit_price' => 30,
    ]);

    $this->assertDatabaseHas('product_deals', [
        'id' => $productDeal->id,
        'quantity' => 5,
        'unit_price' => 30,
    ]);
});

test('destroy deletes the product deal', function () {
    $productDeal = ProductDeal::factory()->create(['created_by' => $this->auth->id]);

    $response = $this->deleteJson(route('product-deals.destroy', $productDeal));

    $response->assertStatus(204);

    $this->assertSoftDeleted('product_deals', [
        'id' => $productDeal->id,
    ]);
});

test('restore deleted product deal', function () {
    $productDeal = ProductDeal::factory()->create(['created_by' => $this->auth->id]);
    $productDeal->delete();

    $this->assertSoftDeleted('product_deals', ['id' => $productDeal->id]);

    $response = $this->postJson(route('product-deals.restore', $productDeal->id));

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'id' => $productDeal->id,
    ]);

    $this->assertDatabaseHas('product_deals', [
        'id' => $productDeal->id,
        'deleted_at' => null,
    ]);
});