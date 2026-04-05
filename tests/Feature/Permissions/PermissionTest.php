<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'permissions.view.all',
        'permissions.create',
        'permissions.update.any',
        'permissions.delete.any',
        'permissions.restore.any',
        'permissions.access.any',
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
test('index returns paginated permissions with roles', function () {
    $role = Role::factory()->create();
    Permission::factory()->count(12)->create()->each(function ($permission) use ($role) {
        $permission->roles()->attach($role);
    });

    $response = $this->getJson(route('api.permissions.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));

    // Check that roles are loaded
    $this->assertArrayHasKey('roles', $response->json('data')[0]);
});

test('index returns all permissions when no pagination specified', function () {
    Permission::factory()->count(3)->create();

    $response = $this->getJson(route('api.permissions.index'));

    $response->assertStatus(200);
    $this->assertCount(9, $response->json('data'));
});

test('index returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.permissions.index'));

    $response->assertStatus(403);
});

/**
 * --------------------------------------------------------------
 * ---------------------------- Show ----------------------------
 * --------------------------------------------------------------
 */
test('show returns a permission with roles', function () {
    $role = Role::factory()->create();
    $permission = Permission::factory()->create();
    $permission->roles()->attach($role);

    $response = $this->getJson(route('api.permissions.show', $permission));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $permission->id]);
    $response->assertJsonStructure([
        'id',
        'name',
        'label',
        'roles',
    ]);
});

test('show returns 403 when user lacks permission', function () {
    $permission = Permission::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.permissions.show', $permission->id));

    $response->assertStatus(403);
});

test('show returns 404 for non-existent permission', function () {
    $response = $this->getJson(route('api.permissions.show', 999999));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Store ---------------------------
 * -------------------------------------------------------------
 */
test('store creates a new permission', function () {
    $payload = [
        'name' => 'view_reports',
        'label' => 'View Reports',
    ];

    $response = $this->postJson(route('api.permissions.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment($payload);
    $this->assertDatabaseHas('permissions', $payload);
});

test('store returns 403 when user lacks permission', function () {
    $permission = Permission::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.permissions.store', $permission->id));

    $response->assertStatus(403);
});

/**
 * ------------------------------------------------------------
 * -------------------------- Update --------------------------
 * ------------------------------------------------------------
 */
test('update modifies an existing permission', function () {
    $permission = Permission::factory()->create([
        'name' => 'edit_users',
        'label' => 'Edit Users',
    ]);

    $payload = [
        'name' => 'edit_accounts',
        'label' => 'Edit Accounts',
    ];

    $response = $this->putJson(route('api.permissions.update', $permission), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment($payload);
    $this->assertDatabaseHas('permissions', $payload);
});

test('update returns 403 when user lacks permission', function () {
    $permission = Permission::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.permissions.update', $permission->id));

    $response->assertStatus(403);
});

test('update returns 404 for non-existent permission', function () {
    $response = $this->putJson(route('api.permissions.update', 999999), ['name' => 'Ghost']);

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Destroy --------------------------
 * -------------------------------------------------------------
 */
test('destroy deletes a permission and detaches roles', function () {
    $role = Role::factory()->create();
    $permission = Permission::factory()->create();
    $permission->roles()->attach($role);

    $response = $this->deleteJson(route('api.permissions.destroy', $permission));

    $response->assertStatus(204);
    $this->assertSoftDeleted('permissions', [
        'id' => $permission->id
    ]);

    $this->assertDatabaseMissing('permission_role', [
        'permission_id' => $permission->id
    ]);
});

test('destroy returns 403 when user lacks permission', function () {
    $permission = Permission::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.permissions.destroy', $permission->id));

    $response->assertStatus(403);
});

test('destroy returns 404 for non-existent permission', function () {
    $response = $this->deleteJson(route('api.permissions.destroy', 999999));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Restore --------------------------
 * -------------------------------------------------------------
 */
test('restore deleted permission', function () {
    $permission = Permission::factory()->create([
        'name' => 'Test Name',
    ]);

    $permission->delete();

    // Ensure it's soft deleted first
    $this->assertSoftDeleted('permissions', ['id' => $permission->id]);

    $response = $this->postJson(route('api.permissions.restore', $permission->id));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $permission->id]);

    $this->assertDatabaseHas('permissions', [
        'id' => $permission->id,
        'deleted_at' => null,
    ]);
});

test('restore returns 403 when user lacks permission', function () {
    $permission = Permission::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.permissions.restore', $permission->id));

    $response->assertStatus(403);
});

test('restore returns 404 for non-existent permission', function () {
    $response = $this->postJson(route('api.permissions.restore', 999999));

    $response->assertStatus(404);
});

test('restore returns 404 when permission is not deleted', function () {
    $permission = Permission::factory()->create();

    $response = $this->postJson(route('api.permissions.restore', $permission->id));

    $response->assertStatus(404);
});
