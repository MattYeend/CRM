<?php

use App\Models\Activity;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();
    
    $permissions = [
        'activities.view.all',
        'activities.create',
        'activities.update.any',
        'activities.delete.any',
        'activities.restore.any',
        'activities.access.any',
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
test('index returns paginated activities and respects per_page query', function () {
    $subject = User::factory()->create();

    Activity::factory()->count(15)->create([
        'assigned_to' => $this->auth->id,
        'subject_type' => User::class,
        'subject_id' => $subject->id,
    ]);

    $response = $this->getJson(route('api.activities.index', ['per_page' => 5]));

    $response->assertStatus(200);

    // Check the response has 5 items (per_page)
    $this->assertCount(5, $response->json('data'));

    // Optional: check that total is correct
    $this->assertEquals(15, $response->json('total'));
});

test('index returns all activities when no pagination specified', function () {
    $subject = User::factory()->create();
    Activity::factory()->count(3)->create([
        'assigned_to' => $this->auth->id,
        'subject_type' => User::class,
        'subject_id' => $subject->id,
    ]);

    $response = $this->getJson(route('api.activities.index'));

    $response->assertStatus(200);
    $this->assertCount(3, $response->json('data'));
});

test('index returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.activities.index'));

    $response->assertStatus(403);
});

/**
 * ------------------------------------------------------------
 * --------------------------- Show ---------------------------
 * ------------------------------------------------------------
 */
test('show returns the activity with user and subject relationships loaded', function () {
    $user = User::factory()->create();
    $subject = User::factory()->create();

    $activity = Activity::factory()->create([
        'assigned_to' => $user->id,
        'subject_type' => 'user',
        'subject_id' => $subject->id,
        'type' => 'sample-type',
        'description' => 'Sample description',
    ]);

    $response = $this->getJson(route('api.activities.show', $activity));
    $response->assertStatus(200);

    $response->assertJsonFragment([
        'id' => $activity->id,
        'type' => 'sample-type',
        'description' => 'Sample description',
        'username' => $user->name,
    ]);

    $response->assertJsonStructure([
        'id',
        'type',
        'description',
        'subject_type',
        'subject_name',
        'username',
        'assigned_to',
        'permissions' => ['view', 'update', 'delete'],
    ]);
});

test('show returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $subject = User::factory()->create();

    $activity = Activity::factory()->create([
        'assigned_to' => $user->id,
        'subject_type' => 'user',
        'subject_id' => $subject->id,
        'type' => 'sample-type',
        'description' => 'Sample description',
    ]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.activities.show', $activity->id));

    $response->assertStatus(403);
});

test('show returns 404 for non-existent activity', function () {
    $response = $this->getJson(route('api.activities.show', 999999));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Store ---------------------------
 * -------------------------------------------------------------
 */
test('store creates an activity with valid payload and returns 201', function () {
    $user = User::factory()->create();

    $payload = [
        'assigned_to' => $user->id,
        'type' => 'created-thing',
        'subject_type' => 'user',
        'subject_id' => $user->id,
        'description' => 'Created an example thing',
        'meta' => ['ip' => '127.0.0.1'],
    ];

    $response = $this->postJson(route('api.activities.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['type' => 'created-thing', 'description' => 'Created an example thing']);

    $this->assertDatabaseHas('activities', [
        'assigned_to' => $user->id,
        'type' => 'created-thing',
        'description' => 'Created an example thing',
    ]);
});

test('store returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $subject = User::factory()->create();

    $activity = Activity::factory()->create([
        'assigned_to' => $user->id,
        'subject_type' => 'user',
        'subject_id' => $subject->id,
        'type' => 'sample-type',
        'description' => 'Sample description',
    ]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.activities.store', $activity->id));

    $response->assertStatus(403);
});

test('store returns 422 when required fields are missing', function () {
    $payload = ['description' => 'no type provided'];

    $response = $this->postJson(route('api.activities.store'), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('type');
});

/**
 * ----------------------------------------------------------
 * ------------------------- Update -------------------------
 * ----------------------------------------------------------
 */
test('update modifies allowed fields and returns the updated activity', function () {
    $subject = User::factory()->create();

    $activity = Activity::factory()->create([
        'assigned_to' => $this->auth->id,
        'subject_type' => User::class,
        'subject_id' => $subject->id,
        'type' => 'old-type',
        'description' => 'old description',
        'meta' => ['a' => 1],
    ]);

    $payload = [
        'type' => 'new-type',
        'description' => 'new description',
        'meta' => ['a' => 2, 'b' => 'added'],
    ];

    $response = $this->patchJson(route('api.activities.update', $activity), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['type' => 'new-type', 'description' => 'new description']);

    $this->assertDatabaseHas('activities', [
        'id' => $activity->id,
        'type' => 'new-type',
        'description' => 'new description',
    ]);
});

test('update returns 403 when user lacks permission', function () {
    $subject = User::factory()->create();

    $activity = Activity::factory()->create([
        'assigned_to' => $this->auth->id,
        'subject_type' => User::class,
        'subject_id' => $subject->id,
        'type' => 'old-type',
        'description' => 'old description',
        'meta' => ['a' => 1],
    ]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.activities.update', $activity->id));

    $response->assertStatus(403);
});

test('update returns 404 for non-existent activity', function () {
    $response = $this->putJson(route('api.activities.update', 999999), ['name' => 'Ghost']);

    $response->assertStatus(404);
});

/**
 * ---------------------------------------------------------
 * ------------------------ Destroy ------------------------
 * ---------------------------------------------------------
 */
test('destroy deletes the activity and returns 204', function () {
    $subject = User::factory()->create();

    $activity = Activity::factory()->create([
        'assigned_to' => $this->auth->id,
        'subject_type' => User::class,
        'subject_id' => $subject->id,
    ]);

    $response = $this->deleteJson(route('api.activities.destroy', $activity));

    $response->assertStatus(204);

    $this->assertSoftDeleted('activities', [
        'id' => $activity->id,
    ]);
});

test('destroy returns 403 when user lacks permission', function () {
    $subject = User::factory()->create();

    $activity = Activity::factory()->create([
        'assigned_to' => $this->auth->id,
        'subject_type' => User::class,
        'subject_id' => $subject->id,
    ]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.activities.destroy', $activity->id));

    $response->assertStatus(403);
});

test('destroy returns 404 for non-existent activity', function () {
    $response = $this->deleteJson(route('api.activities.destroy', 999999));

    $response->assertStatus(404);
});

/**
 * ---------------------------------------------------------
 * ------------------------ Restore ------------------------
 * ---------------------------------------------------------
 */
test('restore deleted activity', function () {
    $subject = User::factory()->create();

    $activity = Activity::factory()->create([
        'assigned_to' => $this->auth->id,
        'subject_type' => User::class,
        'subject_id' => $subject->id,
    ]);

    $activity->delete();

    $this->assertSoftDeleted('activities', [
        'id' => $activity->id,
    ]);

    $response = $this->postJson(route('api.activities.restore', $activity->id));

    $response->assertStatus(200);

    $this->assertDatabaseHas('activities', [
        'id' => $activity->id,
        'deleted_at' => null,
    ]);
});

test('restore returns 403 when user lacks permission', function () {
    $subject = User::factory()->create();

    $activity = Activity::factory()->create([
        'assigned_to' => $this->auth->id,
        'subject_type' => User::class,
        'subject_id' => $subject->id,
    ]);    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.activities.restore', $activity->id));

    $response->assertStatus(403);
});

test('restore returns 404 for non-existent activity', function () {
    $response = $this->postJson(route('api.activities.restore', 999999));

    $response->assertStatus(404);
});

test('restore returns 404 when activity is not deleted', function () {
    $subject = User::factory()->create();

    $activity = Activity::factory()->create([
        'assigned_to' => $this->auth->id,
        'subject_type' => User::class,
        'subject_id' => $subject->id,
    ]);

    $response = $this->postJson(route('api.activities.restore', $activity->id));

    $response->assertStatus(404);
});
