<?php

use App\Models\Company;
use App\Models\Contact;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'contacts.view.all',
        'contacts.create',
        'contacts.update.any',
        'contacts.delete.any',
        'contacts.restore.any',
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

test('index returns paginated contacts and respects per_page query', function () {
    Contact::factory()->count(15)->create();

    $response = $this->getJson(route('contacts.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('index filters contacts by company_id', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    // contacts for company A and B
    Contact::factory()->count(3)->create(['company_id' => $companyA->id]);
    Contact::factory()->count(2)->create(['company_id' => $companyB->id]);

    $response = $this->getJson(route('contacts.index', ['company_id' => $companyA->id]));

    $response->assertStatus(200);

    $data = $response->json('data');
    $this->assertNotEmpty($data);
    // ensure all returned have company_id = companyA
    foreach ($data as $item) {
        $this->assertEquals($companyA->id, $item['company_id']);
    }
});

test('index searches contacts by q (first_name, last_name, email)', function () {
    Contact::factory()->create(['first_name' => 'Alice', 'last_name' => 'Smith', 'email' => 'alice@example.com']);
    Contact::factory()->create(['first_name' => 'Bob', 'last_name' => 'Jones', 'email' => 'bob@example.com']);

    $response = $this->getJson(route('contacts.index', ['q' => 'Alice']));

    $response->assertStatus(200);
    $data = $response->json('data');
    $this->assertCount(1, $data);
    $this->assertStringContainsString('Alice', $data[0]['first_name']);
});

test('show returns the contact with relationships loaded', function () {
    $company = Company::factory()->create();
    $contact = Contact::factory()->create(['company_id' => $company->id]);

    $response = $this->getJson(route('contacts.show', $contact));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $contact->id, 'first_name' => $contact->first_name]);

    $response->assertJsonStructure([
        'id',
        'company_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'job_title',
        'meta',
        'company',      // relationship
        'deals',        // relationship
        'attachments',  // relationship
    ]);
});

test('store creates a contact with valid payload and returns 201', function () {
    $company = Company::factory()->create();

    $payload = [
        'company_id' => $company->id,
        'first_name' => 'Charlie',
        'last_name' => 'Brown',
        'email' => 'charlie@example.com',
        'phone' => '0123456',
        'job_title' => 'Manager',
        'meta' => ['notes' => 'Important contact'],
    ];

    $response = $this->postJson(route('contacts.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['first_name' => 'Charlie', 'email' => 'charlie@example.com']);

    $this->assertDatabaseHas('contacts', [
        'first_name' => 'Charlie',
        'email' => 'charlie@example.com',
        'company_id' => $company->id,
    ]);
});

test('store returns 422 when required fields are missing', function () {
    // Missing required first_name
    $payload = [
        'email' => 'no-name@example.com',
    ];

    $response = $this->postJson(route('contacts.store'), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('first_name');
});

test('update modifies allowed fields and returns the updated contact', function () {
    $contact = Contact::factory()->create([
        'first_name' => 'Old',
        'last_name' => 'Name',
        'email' => 'old@example.com',
    ]);

    $payload = [
        'first_name' => 'New',
        'email' => 'new@example.com',
        'job_title' => 'Director',
    ];

    $response = $this->patchJson(route('contacts.update', $contact), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['first_name' => 'New', 'email' => 'new@example.com']);

    $this->assertDatabaseHas('contacts', [
        'id' => $contact->id,
        'first_name' => 'New',
        'email' => 'new@example.com',
    ]);
});

test('destroy soft deletes the contact and returns 204', function () {
    $contact = Contact::factory()->create();

    $response = $this->deleteJson(route('contacts.destroy', $contact));

    $response->assertStatus(204);

    // Use assertSoftDeleted when model uses SoftDeletes
    $this->assertSoftDeleted('contacts', ['id' => $contact->id]);
});

test('restre deleted company', function () {
    $contact = Contact::factory()->create();
    $contact->delete();

    $this->assertSoftDeleted('contacts', ['id' => $contact->id]);

    $response = $this->postJson(route('contacts.restore', $contact->id));

    $response->assertStatus(200);
    $this->assertNotSoftDeleted('contacts', ['id' => $contact->id]);
});