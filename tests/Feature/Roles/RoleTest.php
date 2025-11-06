<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Authenticate requests (routes are behind sanctum) and disable throttle middleware.
    $this->auth = User::factory()->create();
    $this->actingAs($this->auth, 'sanctum');

    // Routes use throttle:api — disable during tests to avoid rate limiter errors.
    $this->withoutMiddleware(ThrottleRequests::class);
});

test('index returns paginated roles with user_count and permissions', function () {
    // Create some permissions and roles
    $permission = Permission::factory()->create();
    Role::factory()->count(12)->create()->each(function ($role) use ($permission) {
        $role->permissions()->attach($permission);
    });

    $response = $this->getJson(route('roles.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);

    // Should return 5 items in the data array
    $this->assertCount(5, $response->json('data'));

    // Ensure returned item has permissions and users_count keys (users_count comes from withCount)
    $first = $response->json('data')[0];
    $this->assertArrayHasKey('permissions', $first);
    $this->assertArrayHasKey('users_count', $first);
});

test('show returns a role with permissions and users', function () {
    $permission = Permission::factory()->create();
    $role = Role::factory()->create();
    $role->permissions()->attach($permission);

    $response = $this->getJson(route('roles.show', $role));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $role->id]);
    $response->assertJsonStructure([
        'id',
        'name',
        'label',
        'permissions' => [],
        'users' => [],
    ]);
});

test('store creates a new role and can attach permissions', function () {
    $permA = Permission::factory()->create();
    $permB = Permission::factory()->create();

    $payload = [
        'name' => 'sales_rep',
        'label' => 'Sales Representative',
        'permissions' => [$permA->id, $permB->id],
    ];

    $response = $this->postJson(route('roles.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['name' => 'sales_rep', 'label' => 'Sales Representative']);

    // Role exists
    $roleId = $response->json('id');
    $this->assertDatabaseHas('roles', ['id' => $roleId, 'name' => 'sales_rep']);

    // Pivot entries exist (common pivot name is permission_role — adjust if different in your app)
    $this->assertDatabaseHas('permission_role', ['permission_id' => $permA->id, 'role_id' => $roleId]);
    $this->assertDatabaseHas('permission_role', ['permission_id' => $permB->id, 'role_id' => $roleId]);
});

test('update modifies an existing role and syncs permissions', function () {
    $permOld = Permission::factory()->create();
    $permNew = Permission::factory()->create();

    $role = Role::factory()->create([
        'name' => 'old_role',
        'label' => 'Old Role',
    ]);
    $role->permissions()->attach($permOld);

    $payload = [
        'name' => 'new_role',
        'label' => 'New Role Label',
        'permissions' => [$permNew->id],
    ];

    $response = $this->putJson(route('roles.update', $role), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['name' => 'new_role', 'label' => 'New Role Label']);

    $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'new_role']);
    // Old pivot should be removed, new pivot should exist
    $this->assertDatabaseMissing('permission_role', ['permission_id' => $permOld->id, 'role_id' => $role->id]);
    $this->assertDatabaseHas('permission_role', ['permission_id' => $permNew->id, 'role_id' => $role->id]);
});

test('destroy deletes the role and detaches permissions and users', function () {
    $role = Role::factory()->create();
    $permission = Permission::factory()->create();
    $role->permissions()->attach($permission);

    // Optionally attach a user to this role if your pivot exists (role_user)
    $user = User::factory()->create();
    $role->users()->attach($user);

    $response = $this->deleteJson(route('roles.destroy', $role));

    $response->assertStatus(204);

    $this->assertDatabaseMissing('roles', ['id' => $role->id]);

    // pivot rows should be removed
    $this->assertDatabaseMissing('permission_role', ['role_id' => $role->id]);
    $this->assertDatabaseMissing('role_user', ['role_id' => $role->id]);
});