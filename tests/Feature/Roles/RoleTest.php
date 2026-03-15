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
        'roles.view.all',
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