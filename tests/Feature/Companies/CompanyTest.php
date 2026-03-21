<?php

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'companies.view.all',
        'companies.create',
        'companies.update.any',
        'companies.delete.any',
        'companies.restore.any',
    ];

    $permissionModels = collect($permissions)
        ->map(fn($name) => Permission::firstOrCreate(['name' => $name]));

    $role = Role::factory()->create(['name' => 'admin']);
    $role->permissions()->sync($permissionModels->pluck('id'));

    $this->auth->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($this->auth, 'sanctum');

    $this->withoutMiddleware(ThrottleRequests::class);
});

test('index returns paginated companies and respects per_page query', function () {
    Company::factory()->count(15)->create();

    $response = $this->getJson(route('api.companies.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('index filters companies by q query parameter (search by name)', function () {
    Company::factory()->create(['name' => 'Alpha Industries']);
    Company::factory()->create(['name' => 'Beta Solutions']);
    Company::factory()->create(['name' => 'Gamma Labs']);

    $response = $this->getJson(route('api.companies.index', ['q' => 'Beta']));

    $response->assertStatus(200);

    $data = $response->json('data');
    $this->assertCount(1, $data);
    $this->assertStringContainsString('Beta', $data[0]['name']);
});

test('show returns the company with relationships loaded', function () {
    $company = Company::factory()->create();

    $response = $this->getJson(route('api.companies.show', $company));

    $response->assertStatus(200);

    $response->assertJsonFragment(['id' => $company->id, 'name' => $company->name]);

    $response->assertJsonStructure([
        'id',
        'name',
        'industry',
        'website',
        'phone',
        'address',
        'city',
        'region',
        'postal_code',
        'country',
        'meta',
        'contacts',
        'deals',
        'invoices',
        'attachments'
    ]);
});

test('store creates a company with valid payload and returns 201', function () {
    $payload = [
        'name' => 'NewCo Ltd',
        'industry' => 'Software',
        'website' => 'https://newco.example',
        'phone' => '0123456789',
        'address' => '1 Test Street',
        'city' => 'Testville',
        'region' => 'Testshire',
        'postal_code' => 'TST123',
        'country' => 'UK',
        'meta' => ['notes' => 'Important client'],
    ];

    $response = $this->postJson(route('api.companies.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['name' => 'NewCo Ltd', 'industry' => 'Software']);

    $this->assertDatabaseHas('companies', ['name' => 'NewCo Ltd', 'website' => 'https://newco.example']);
});

test('store returns 422 when required fields are missing', function () {
    $payload = [
        'industry' => 'Software',
    ];

    $response = $this->postJson(route('api.companies.store'), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('name');
});

test('update modifies allowed fields and returns the updated company', function () {
    $company = Company::factory()->create([
        'name' => 'Old Name Ltd',
        'website' => 'https://old.example',
    ]);

    $payload = [
        'name' => 'Updated Name Ltd',
        'website' => 'https://updated.example',
        'meta' => ['tier' => 'gold'],
    ];

    $response = $this->patchJson(route('api.companies.update', $company), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['name' => 'Updated Name Ltd', 'website' => 'https://updated.example']);

    $this->assertDatabaseHas('companies', [
        'id' => $company->id,
        'name' => 'Updated Name Ltd',
    ]);
});

test('destroy soft deletes the company and returns 204', function () {
    $company = Company::factory()->create();

    $response = $this->deleteJson(route('api.companies.destroy', $company));

    $response->assertStatus(204);

    $this->assertSoftDeleted('companies', ['id' => $company->id]);
});

test('restore brings back a soft-deleted company', function () {
    $company = Company::factory()->create();
    $company->delete();

    // Ensure it's soft deleted first
    $this->assertSoftDeleted('companies', ['id' => $company->id]);

    $response = $this->postJson(route('api.companies.restore', $company->id));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $company->id]);

    $this->assertDatabaseHas('companies', ['id' => $company->id, 'name' => $company->name]);
});