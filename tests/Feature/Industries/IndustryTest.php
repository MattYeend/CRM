<?php

use App\Models\Industry;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'industries.view.all',
        'industries.create',
        'industries.update.any',
        'industries.delete.any',
        'industries.restore.any',
        'industries.access.any',
    ];

    $permissionModels = collect($permissions)
        ->map(fn($name) => Permission::firstOrCreate(['name' => $name]));

    $role = Role::factory()->create(['name' => 'admin']);
    $role->permissions()->sync($permissionModels->pluck('id'));

    $this->auth->update(['role_id' => $role->id]);

    $this->actingAs($this->auth, 'sanctum');
    $this->withoutMiddleware(ThrottleRequests::class);
});

// Index
test('index returns paginated industries', function () {
    Industry::factory()->count(15)->create();

    $response = $this->getJson(route('api.industries.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('index filters by q parameter', function () {
    Industry::factory()->create(['name' => 'Technology', 'slug' => 'technology']);
    Industry::factory()->create(['name' => 'Healthcare', 'slug' => 'healthcare']);

    $response = $this->getJson(route('api.industries.index', ['q' => 'Tech']));

    $response->assertStatus(200);
    $this->assertCount(1, $response->json('data'));
});

test('index returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);
    $user->update(['role_id' => $role->id]);
    $this->actingAs($user, 'sanctum');

    $this->getJson(route('api.industries.index'))->assertStatus(403);
});

// Show
test('show returns the industry', function () {
    $industry = Industry::factory()->create();

    $response = $this->getJson(route('api.industries.show', $industry));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $industry->id, 'name' => $industry->name]);
});

test('show returns 404 for non-existent industry', function () {
    $this->getJson(route('api.industries.show', 999999))->assertStatus(404);
});

// Store
test('store creates an industry and returns 201', function () {
    $response = $this->postJson(route('api.industries.store'), ['name' => 'Logistics']);

    $response->assertStatus(201);
    $response->assertJsonFragment(['name' => 'Logistics']);
    $this->assertDatabaseHas('industries', ['name' => 'Logistics', 'slug' => 'logistics']);
});

test('store returns 422 when name is missing', function () {
    $this->postJson(route('api.industries.store'), [])
        ->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

test('store returns 422 when name is duplicate', function () {
    Industry::factory()->create(['name' => 'Finance', 'slug' => 'finance']);

    $this->postJson(route('api.industries.store'), ['name' => 'Finance'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

test('store returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);
    $user->update(['role_id' => $role->id]);
    $this->actingAs($user, 'sanctum');

    $this->postJson(route('api.industries.store'), ['name' => 'Logistics'])
        ->assertStatus(403);
});

// Update
test('update modifies the industry and returns 200', function () {
    $industry = Industry::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

    $response = $this->patchJson(route('api.industries.update', $industry), ['name' => 'New Name']);

    $response->assertStatus(200);
    $response->assertJsonFragment(['name' => 'New Name']);
    $this->assertDatabaseHas('industries', ['id' => $industry->id, 'slug' => 'new-name']);
});

test('update returns 404 for non-existent industry', function () {
    $this->patchJson(route('api.industries.update', 999999), ['name' => 'Ghost'])
        ->assertStatus(404);
});

test('update returns 403 when user lacks permission', function () {
    $industry = Industry::factory()->create();
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);
    $user->update(['role_id' => $role->id]);
    $this->actingAs($user, 'sanctum');

    $this->patchJson(route('api.industries.update', $industry), ['name' => 'New Name'])
        ->assertStatus(403);
});

// Destroy
test('destroy soft deletes the industry and returns 204', function () {
    $industry = Industry::factory()->create();

    $this->deleteJson(route('api.industries.destroy', $industry))->assertStatus(204);

    $this->assertSoftDeleted('industries', ['id' => $industry->id]);
});

test('destroy returns 404 for non-existent industry', function () {
    $this->deleteJson(route('api.industries.destroy', 999999))->assertStatus(404);
});

test('destroy returns 403 when user lacks permission', function () {
    $industry = Industry::factory()->create();
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);
    $user->update(['role_id' => $role->id]);
    $this->actingAs($user, 'sanctum');

    $this->deleteJson(route('api.industries.destroy', $industry))->assertStatus(403);
});

// Restore
test('restore brings back a soft-deleted industry', function () {
    $industry = Industry::factory()->create();
    $industry->delete();

    $this->assertSoftDeleted('industries', ['id' => $industry->id]);

    $response = $this->postJson(route('api.industries.restore', $industry->id));

    $response->assertStatus(200);
    $this->assertDatabaseHas('industries', ['id' => $industry->id, 'deleted_at' => null]);
});

test('restore returns 404 when industry is not deleted', function () {
    $industry = Industry::factory()->create();

    $this->postJson(route('api.industries.restore', $industry->id))->assertStatus(404);
});

test('restore returns 404 for non-existent industry', function () {
    $this->postJson(route('api.industries.restore', 999999))->assertStatus(404);
});

test('restore returns 403 when user lacks permission', function () {
    $industry = Industry::factory()->create();
    $industry->delete();
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);
    $user->update(['role_id' => $role->id]);
    $this->actingAs($user, 'sanctum');

    $this->postJson(route('api.industries.restore', $industry->id))->assertStatus(403);
});
