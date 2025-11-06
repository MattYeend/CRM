<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Authenticate requests (routes are behind sanctum) and disable throttle middleware.
    $this->auth = User::factory()->create();
    $this->actingAs($this->auth, 'sanctum');

    $this->withoutMiddleware(ThrottleRequests::class);
});

test('index returns paginated products', function () {
    // Ensure factory creates valid products
    Product::factory()->count(12)->create();

    $response = $this->getJson(route('products.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);

    // Should return 5 items
    $this->assertCount(5, $response->json('data'));
});

test('show returns a single product', function () {
    $product = Product::factory()->create();

    $response = $this->getJson(route('products.show', $product));

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

    $response = $this->postJson(route('products.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['name' => 'Test Product', 'sku' => 'SKU-1234']);
    $this->assertDatabaseHas('products', ['name' => 'Test Product', 'sku' => 'SKU-1234']);
});

test('store returns validation error when required fields missing', function () {
    // missing 'name'
    $payload = [
        'sku' => 'SKU-9999',
    ];

    $response = $this->postJson(route('products.store'), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('name');
});

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

    $response = $this->putJson(route('products.update', $product), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['name' => 'Updated Product', 'price' => 25.5]);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Updated Product']);
});

test('destroy deletes the product', function () {
    $product = Product::factory()->create();

    $response = $this->deleteJson(route('products.destroy', $product));

    $response->assertStatus(204);

    $this->assertSoftDeleted('products', ['id' => $product->id]);
});