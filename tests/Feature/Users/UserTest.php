<?php

use App\Http\Controllers\UserController;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Users\UserManagementService;
use App\Services\Users\UserQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'users.view.all',
        'users.create',
        'users.update.any',
        'users.delete.any',
        'users.restore.any',
        'users.manage',
        'users.assign.roles',
        'users.assign.permissions',
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
test('index calls query service and returns list', function () {
    $users = collect([
        ['id' => 10, 'name' => 'Alice', 'email' => 'alice@example.test']
    ]);

    $paginator = new LengthAwarePaginator(
        $users,
        $users->count(),
        10,
        1
    );

    $queryServiceMock = Mockery::mock(UserQueryService::class);
    $queryServiceMock->shouldReceive('list')
        ->once()
        ->with(Mockery::type(Request::class))
        ->andReturn($paginator);

    $this->app->instance(UserQueryService::class, $queryServiceMock);

    $response = $this->getJson(route('api.users.index'));

    $response->assertStatus(200);
    $response->assertJsonFragment(['name' => 'Alice', 'email' => 'alice@example.test']);
});

test('index returns all users when no pagination specified', function () {
    User::factory()->count(3)->create();

    $response = $this->getJson(route('api.users.index'));

    $response->assertStatus(200);
    $this->assertCount(4, $response->json('data'));
});

test('index returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.users.index'));

    $response->assertStatus(403);
});

/**
 * --------------------------------------------------------------
 * ---------------------------- Show ----------------------------
 * --------------------------------------------------------------
 */
test('show calls query service and returns single user', function () {
    $user = User::factory()->create(['name' => 'Bob', 'email' => 'bob@example.test']);

    $queryServiceMock = Mockery::mock(UserQueryService::class);
    $queryServiceMock->shouldReceive('show')
        ->once()
        ->with(Mockery::on(fn($arg) => $arg instanceof User && $arg->id === $user->id))
        ->andReturn([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url,
            'job_title' => $user->jobTitle,
            'role' => $user->role,
            'permissions' => [
                'view' => true,
                'update' => true,
                'delete' => true,
            ],
        ]);

    $this->app->instance(UserQueryService::class, $queryServiceMock);

    $response = $this->getJson(route('api.users.show', $user));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $user->id, 'name' => 'Bob']);
    $response->assertJsonStructure(['id', 'name', 'email', 'role']);
});

test('show returns 403 when user lacks permission', function () {
    $showUser = User::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.users.show', $showUser->id));

    $response->assertStatus(403);
});

test('show returns 404 for non-existent user', function () {
    $response = $this->getJson(route('api.users.show', 999999));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Store ---------------------------
 * -------------------------------------------------------------
 */
test('store calls management service and returns 201 with user', function () {
    $managementMock = Mockery::mock(UserManagementService::class);

    $payload = [
        'name' => 'New User',
        'email' => 'new@example.test',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    // Return an actual User instance (not stdClass) to satisfy typed return
    $returnedUser = User::factory()->make([
        'id' => 123,
        'name' => 'New User',
        'email' => 'new@example.test',
    ]);

    $managementMock->shouldReceive('store')
        ->once()
        ->with(Mockery::type(Request::class))
        ->andReturn($returnedUser);

    $this->app->instance(UserManagementService::class, $managementMock);

    $response = $this->postJson(route('api.users.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['id' => 123, 'name' => 'New User', 'email' => 'new@example.test']);
});

test('store returns 403 when user lacks permission', function () {
    $showUser = User::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.users.store', $showUser->id));

    $response->assertStatus(403);
});

/**
 * --------------------------------------------------------------
 * --------------------------- Update ---------------------------
 * --------------------------------------------------------------
 */
test('update calls management service and returns updated user', function () {
    $existing = User::factory()->create();

    $managementMock = Mockery::mock(UserManagementService::class);

    $updatePayload = ['name' => 'Updated Name'];

    // Return a User instance to satisfy the method signature
    $returned = User::factory()->make([
        'id' => $existing->id,
        'name' => 'Updated Name',
        'email' => $existing->email,
    ]);

    $managementMock->shouldReceive('update')
        ->once()
        ->with(Mockery::type(Request::class), Mockery::on(function ($arg) use ($existing) {
            return $arg instanceof User && $arg->id === $existing->id;
        }))
        ->andReturn($returned);

    $this->app->instance(UserManagementService::class, $managementMock);

    $response = $this->putJson(route('api.users.update', $existing), $updatePayload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $existing->id, 'name' => 'Updated Name']);
});

test('update returns 403 when user lacks permission', function () {
    $showUser = User::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.users.update', $showUser->id));

    $response->assertStatus(403);
});

test('update returns 404 for non-existent user', function () {
    $response = $this->putJson(route('api.users.update', 999999), ['name' => 'Ghost']);

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Destroy --------------------------
 * -------------------------------------------------------------
 */
test('destroy calls management service and returns 204', function () {
    $existing = User::factory()->create();

    $managementMock = Mockery::mock(UserManagementService::class);
    $managementMock->shouldReceive('destroy')
        ->once()
        ->with(Mockery::on(function ($arg) use ($existing) {
            return $arg instanceof User && $arg->id === $existing->id;
        }))
        ->andReturnNull();

    $this->app->instance(UserManagementService::class, $managementMock);

    $response = $this->deleteJson(route('api.users.destroy', $existing));

    $response->assertStatus(204);
});

test('destroy returns 403 when user lacks permission', function () {
    $showUser = User::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.users.destroy', $showUser->id));

    $response->assertStatus(403);
});

test('destroy returns 404 for non-existent user', function () {
    $response = $this->deleteJson(route('api.users.destroy', 999999));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Restore --------------------------
 * -------------------------------------------------------------
 */
test('restore calls management service and returns restored user', function () {
    $user = User::factory()->create([
        'id' => 77,
        'name' => 'Restored',
        'email' => 'restored@example.test',
    ]);
    $user->delete();

    $managementMock = Mockery::mock(UserManagementService::class);

    $managementMock->shouldReceive('restore')
        ->once()
        ->with(77)
        ->andReturn($user);

    $this->app->instance(UserManagementService::class, $managementMock);

    $response = $this->postJson(route('api.users.restore', 77));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => 77, 'name' => 'Restored']);
});

test('restore returns 403 when user lacks permission', function () {
    $showUser = User::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.users.restore', $showUser->id));

    $response->assertStatus(403);
});

test('restore returns 404 for non-existent user', function () {
    $response = $this->postJson(route('api.users.restore', 999999));

    $response->assertStatus(404);
});

test('restore returns 404 when user is not deleted', function () {
    $user = User::factory()->create();

    $response = $this->postJson(route('api.users.restore', $user->id));

    $response->assertStatus(404);
});
