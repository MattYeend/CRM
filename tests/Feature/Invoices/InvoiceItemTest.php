<?php

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create an authenticated user for API requests
    $this->auth = User::factory()->create();
    $this->actingAs($this->auth, 'sanctum');

    // Disable throttle middleware for tests
    $this->withoutMiddleware(ThrottleRequests::class);
});

test('index returns paginated invoice items with relations', function () {
    $invoice = Invoice::factory()->create();
    $product = Product::factory()->create();

    InvoiceItem::factory()->count(12)->create([
        'invoice_id' => $invoice->id,
        'product_id' => $product->id,
    ]);

    $response = $this->getJson(route('invoice-items.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('show returns an invoice item with relations loaded', function () {
    $invoice = Invoice::factory()->create();
    $product = Product::factory()->create();

    $invoiceItem = InvoiceItem::factory()->create([
        'invoice_id' => $invoice->id,
        'product_id' => $product->id,
    ]);

    $response = $this->getJson(route('invoice-items.show', $invoiceItem));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $invoiceItem->id]);
    $response->assertJsonStructure([
        'id',
        'invoice_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'line_total',
        'invoice' => [],
        'product' => [],
    ]);
});

test('store creates a new invoice item and returns 201', function () {
    $invoice = Invoice::factory()->create();
    $product = Product::factory()->create();

    $payload = [
        'invoice_id' => $invoice->id,
        'product_id' => $product->id,
        'description' => 'Test Item',
        'quantity' => 3,
        'unit_price' => 50,
    ];

    $response = $this->postJson(route('invoice-items.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment([
        'description' => 'Test Item',
        'quantity' => 3,
        'unit_price' => 50,
        'line_total' => 150, // 3 * 50
    ]);
    $this->assertDatabaseHas('invoice_items', ['description' => 'Test Item']);
});

test('update modifies an existing invoice item', function () {
    $invoice = Invoice::factory()->create();
    $product = Product::factory()->create();

    $invoiceItem = InvoiceItem::factory()->create([
        'invoice_id' => $invoice->id,
        'product_id' => $product->id,
        'description' => 'Old Item',
        'quantity' => 2,
        'unit_price' => 25,
        'line_total' => 50,
    ]);

    $payload = [
        'description' => 'Updated Item',
        'quantity' => 4,
        'unit_price' => 30,
    ];

    $response = $this->putJson(route('invoice-items.update', $invoiceItem), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'description' => 'Updated Item',
        'quantity' => 4,
        'unit_price' => 30,
        'line_total' => 120, // 4 * 30
    ]);
    $this->assertDatabaseHas('invoice_items', [
        'id' => $invoiceItem->id,
        'description' => 'Updated Item',
    ]);
});

test('destroy deletes an invoice item', function () {
    $invoice = Invoice::factory()->create();
    $product = Product::factory()->create();

    $invoiceItem = InvoiceItem::factory()->create([
        'invoice_id' => $invoice->id,
        'product_id' => $product->id,
    ]);

    $response = $this->deleteJson(route('invoice-items.destroy', $invoiceItem));

    $response->assertStatus(204);

    $this->assertDatabaseMissing('invoice_items', ['id' => $invoiceItem->id]);
});