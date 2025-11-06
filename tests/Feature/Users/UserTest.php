<?php

use App\Http\Controllers\UserController;
use App\Models\User;
use App\Services\UserManagementService;
use App\Services\UserQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    // create an authenticated user (routes expect auth)
    $this->auth = User::factory()->create();
    $this->actingAs($this->auth, 'sanctum');

    // disable throttle middleware (routes use throttle:api)
    $this->withoutMiddleware(ThrottleRequests::class);
});

test('index calls query service and returns list', function () {
    $queryServiceMock = Mockery::mock(UserQueryService::class);

    // Return an arbitrary structure the controller will json-encode
    $fakeList = [
        'data' => [
            ['id' => 10, 'name' => 'Alice', 'email' => 'alice@example.test']
        ],
        'per_page' => 10,
    ];

    $queryServiceMock->shouldReceive('list')
        ->once()
        ->with(Mockery::type(Request::class))
        ->andReturn($fakeList);

    $this->app->instance(UserQueryService::class, $queryServiceMock);

    $response = $this->getJson(route('users.index'));

    $response->assertStatus(200);
    $response->assertJsonFragment(['name' => 'Alice', 'email' => 'alice@example.test']);
});

test('show calls query service and returns single user', function () {
    $user = User::factory()->create(['name' => 'Bob', 'email' => 'bob@example.test']);

    $queryServiceMock = Mockery::mock(UserQueryService::class);
    $queryServiceMock->shouldReceive('show')
        ->once()
        ->with(Mockery::on(function ($arg) use ($user) {
            return $arg instanceof User && $arg->id === $user->id;
        }))
        ->andReturn([
            'id' => $user->id,
            'name' => 'Bob',
            'email' => 'bob@example.test',
            'roles' => [],
        ]);

    $this->app->instance(UserQueryService::class, $queryServiceMock);

    $response = $this->getJson(route('users.show', $user));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $user->id, 'name' => 'Bob']);
    $response->assertJsonStructure(['id', 'name', 'email', 'roles']);
});

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

    $response = $this->postJson(route('users.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['id' => 123, 'name' => 'New User', 'email' => 'new@example.test']);
});

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

    $response = $this->putJson(route('users.update', $existing), $updatePayload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $existing->id, 'name' => 'Updated Name']);
});

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

    $response = $this->deleteJson(route('users.destroy', $existing));

    $response->assertStatus(204);
});

test('restore calls management service and returns restored user', function () {
    $managementMock = Mockery::mock(UserManagementService::class);

    // Return an actual User model (made, not persisted)
    $restored = User::factory()->make([
        'id' => 77,
        'name' => 'Restored',
        'email' => 'restored@example.test',
    ]);

    $managementMock->shouldReceive('restore')
        ->once()
        ->with(Mockery::type('int'))
        ->andReturn($restored);

    $this->app->instance(UserManagementService::class, $managementMock);

    $response = $this->postJson(route('users.restore', 77));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => 77, 'name' => 'Restored']);
});

test('forceDelete calls management service and returns 204', function () {
    $managementMock = Mockery::mock(UserManagementService::class);

    $managementMock->shouldReceive('forceDelete')
        ->once()
        ->with(123)
        ->andReturnNull();

    $this->app->instance(UserManagementService::class, $managementMock);

    $response = $this->deleteJson(route('users.forceDelete', 123));

    $response->assertStatus(204);
});

test('attachRoles calls management service and returns user with roles (controller call)', function () {
    // Create an existing user to pass to controller
    $existing = User::factory()->create();

    $managementMock = Mockery::mock(UserManagementService::class);

    // Return a User model instance that includes roles on ->toArray()
    $userWithRoles = User::factory()->make([
        'id' => $existing->id,
        'name' => $existing->name,
        'email' => $existing->email,
    ]);

    // attach 'roles' attribute so JSON response contains roles
    $userWithRoles->setAttribute('roles', [['id' => 1, 'name' => 'admin']]);

    $managementMock->shouldReceive('attachRoles')
        ->once()
        ->with(Mockery::type(Request::class), Mockery::on(function ($arg) use ($existing) {
            return $arg instanceof User && $arg->id === $existing->id;
        }))
        ->andReturn($userWithRoles);

    $this->app->instance(UserManagementService::class, $managementMock);

    // Build a request payload for roles
    $payload = ['roles' => [1]];

    // Resolve a controller instance so non-static method can be called
    $controller = $this->app->make(UserController::class);

    // Call the controller method via container to resolve method dependencies
    $response = $this->app->call(
        [$controller, 'attachRoles'],
        ['request' => new Request($payload), 'user' => $existing]
    );

    $this->assertEquals(200, $response->getStatusCode());
    $content = json_decode($response->getContent(), true);
    $this->assertEquals($existing->id, $content['id']);
    $this->assertEquals([['id' => 1, 'name' => 'admin']], $content['roles']);
});

test('detachRoles calls management service and returns user without those roles (controller call)', function () {
    $existing = User::factory()->create();

    $managementMock = Mockery::mock(UserManagementService::class);

    $userNoRoles = User::factory()->make([
        'id' => $existing->id,
        'name' => $existing->name,
        'email' => $existing->email,
    ]);
    $userNoRoles->setAttribute('roles', []);

    $managementMock->shouldReceive('detachRoles')
        ->once()
        ->with(Mockery::type(Request::class), Mockery::on(function ($arg) use ($existing) {
            return $arg instanceof User && $arg->id === $existing->id;
        }))
        ->andReturn($userNoRoles);

    $this->app->instance(UserManagementService::class, $managementMock);

    $payload = ['roles' => [1]];

    $controller = $this->app->make(UserController::class);

    $response = $this->app->call(
        [$controller, 'detachRoles'],
        ['request' => new Request($payload), 'user' => $existing]
    );

    $this->assertEquals(200, $response->getStatusCode());
    $content = json_decode($response->getContent(), true);
    $this->assertEquals($existing->id, $content['id']);
    $this->assertEquals([], $content['roles']);
});