<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Authenticated user for API requests
    $this->auth = User::factory()->create();
    $this->actingAs($this->auth, 'sanctum');

    // Disable throttle middleware for tests
    $this->withoutMiddleware(ThrottleRequests::class);
});

test('index returns paginated permissions with roles', function () {
    $role = Role::factory()->create();
    Permission::factory()->count(12)->create()->each(function ($permission) use ($role) {
        $permission->roles()->attach($role);
    });

    $response = $this->getJson(route('permissions.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));

    // Check that roles are loaded
    $this->assertArrayHasKey('roles', $response->json('data')[0]);
});

test('show returns a permission with roles', function () {
    $role = Role::factory()->create();
    $permission = Permission::factory()->create();
    $permission->roles()->attach($role);

    $response = $this->getJson(route('permissions.show', $permission));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $permission->id]);
    $response->assertJsonStructure([
        'id',
        'name',
        'label',
        'roles',
    ]);
});

test('store creates a new permission', function () {
    $payload = [
        'name' => 'view_reports',
        'label' => 'View Reports',
    ];

    $response = $this->postJson(route('permissions.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment($payload);
    $this->assertDatabaseHas('permissions', $payload);
});

test('update modifies an existing permission', function () {
    $permission = Permission::factory()->create([
        'name' => 'edit_users',
        'label' => 'Edit Users',
    ]);

    $payload = [
        'name' => 'edit_accounts',
        'label' => 'Edit Accounts',
    ];

    $response = $this->putJson(route('permissions.update', $permission), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment($payload);
    $this->assertDatabaseHas('permissions', $payload);
});

test('destroy deletes a permission and detaches roles', function () {
    $role = Role::factory()->create();
    $permission = Permission::factory()->create();
    $permission->roles()->attach($role);

    $response = $this->deleteJson(route('permissions.destroy', $permission));

    $response->assertStatus(204);
    $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
    $this->assertDatabaseMissing('permission_role', ['permission_id' => $permission->id]);
});