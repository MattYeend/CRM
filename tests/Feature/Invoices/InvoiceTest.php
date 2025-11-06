<?php

use App\Models\Invoice;
use App\Models\User;
use App\Models\Company;
use App\Models\Contact;
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

test('index returns paginated invoices with relations', function () {
    $company = Company::factory()->create();
    Invoice::factory()->count(12)->create(['company_id' => $company->id]);

    $response = $this->getJson(route('invoices.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('show returns an invoice with relations loaded', function () {
    $invoice = Invoice::factory()->create();

    $response = $this->getJson(route('invoices.show', $invoice));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $invoice->id]);
    $response->assertJsonStructure([
        'id',
        'number',
        'status',
        'total',
        'currency',
        'company' => [],
        'contact' => [],
        'items' => [],
    ]);
});

test('store creates a new invoice and returns 201', function () {
    $company = Company::factory()->create();
    $contact = Contact::factory()->create();

    $payload = [
        'number' => 'INV-1001',
        'company_id' => $company->id,
        'contact_id' => $contact->id,
        'created_by' => $this->auth->id,
        'status' => 'draft',
        'subtotal' => 100,
        'tax' => 10,
        'total' => 110,
        'currency' => 'USD',
    ];

    $response = $this->postJson(route('invoices.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['number' => 'INV-1001', 'status' => 'draft']);
    $this->assertDatabaseHas('invoices', ['number' => 'INV-1001', 'created_by' => $this->auth->id]);
});

test('update modifies an existing invoice', function () {
    $invoice = Invoice::factory()->create(['number' => 'INV-2000', 'status' => 'draft']);

    $payload = [
        'number' => 'INV-2001',
        'status' => 'sent',
    ];

    $response = $this->putJson(route('invoices.update', $invoice), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['number' => 'INV-2001', 'status' => 'sent']);
    $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'number' => 'INV-2001', 'status' => 'sent']);
});

test('destroy deletes an invoice', function () {
    $invoice = Invoice::factory()->create();

    $response = $this->deleteJson(route('invoices.destroy', $invoice));

    $response->assertStatus(204);

    // If soft deletes are used
    $this->assertSoftDeleted('invoices', ['id' => $invoice->id]);
});