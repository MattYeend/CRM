<?php

use App\Models\Deal;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'tasks.view.all',
        'tasks.create',
        'tasks.update.any',
        'tasks.delete.any',
        'tasks.restore.any',
        'tasks.access.any',
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
test('index returns paginated tasks with relations', function () {
    $assignee = User::factory()->create();
    $creator = User::factory()->create();
    $taskable = Deal::factory()->create();

    Task::factory()->count(12)->create([
        'assigned_to' => $assignee->id,
        'created_by' => $creator->id,
        'taskable_type' => Deal::class,
        'taskable_id' => $taskable->id,
        'created_by' => $this->auth->id,
    ]);

    $response = $this->getJson(route('api.tasks.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));

    // ensure relations are present on first item
    $first = $response->json('data')[0];
    $this->assertArrayHasKey('assignee', $first);
    $this->assertArrayHasKey('creator', $first);
    $this->assertArrayHasKey('taskable', $first);
});

test('index returns all tasks when no pagination specified', function () {
    $assignee = User::factory()->create();
    $creator = User::factory()->create();
    $taskable = Deal::factory()->create();

    Task::factory()->count(3)->create([
        'assigned_to' => $assignee->id,
        'created_by' => $creator->id,
        'taskable_type' => Deal::class,
        'taskable_id' => $taskable->id,
        'created_by' => $this->auth->id,
    ]);
    $response = $this->getJson(route('api.tasks.index'));

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

    $response = $this->getJson(route('api.tasks.index'));

    $response->assertStatus(403);
});

/**
 * --------------------------------------------------------------
 * ---------------------------- Show ----------------------------
 * --------------------------------------------------------------
 */
test('show returns a task with assignee, creator and taskable loaded', function () {
    $assignee = User::factory()->create();
    $creator = User::factory()->create();
    $taskable = Deal::factory()->create();

    $task = Task::factory()->create([
        'assigned_to' => $assignee->id,
        'created_by' => $creator->id,
        'taskable_type' => Deal::class,
        'taskable_id' => $taskable->id,
    ]);

    $response = $this->getJson(route('api.tasks.show', $task));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $task->id]);
    $response->assertJsonStructure([
        'id',
        'title',
        'description',
        'assigned_to',
        'created_by',
        'priority',
        'status',
        'due_at',
        'assignee' => [],
        'creator' => [],
        'taskable' => [],
    ]);
});

test('show returns 403 when user lacks permission', function () {
    $assignee = User::factory()->create();
    $creator = User::factory()->create();
    $taskable = Deal::factory()->create();

    $task = Task::factory()->create([
        'assigned_to' => $assignee->id,
        'created_by' => $creator->id,
        'taskable_type' => Deal::class,
        'taskable_id' => $taskable->id,
        'created_by' => $this->auth->id,
    ]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.tasks.show', $task->id));

    $response->assertStatus(403);
});

test('show returns 404 for non-existent task', function () {
    $response = $this->getJson(route('api.tasks.show', 999999));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Store ---------------------------
 * -------------------------------------------------------------
 */
test('store creates a task, handles polymorphic assignment and returns 201', function () {
    $assignee = User::factory()->create();
    $creator = User::factory()->create();
    $taskable = Deal::factory()->create();

    $payload = [
        'title' => 'Follow up',
        'description' => 'Call the client to confirm details',
        'assigned_to' => $assignee->id,
        'created_by' => $creator->id,
        'taskable_type' => Deal::class,
        'taskable_id' => $taskable->id,
        'priority' => 'high',
        'status' => 'pending',
        'due_at' => now()->addDays(3)->toDateTimeString(),
    ];

    $response = $this->postJson(route('api.tasks.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['title' => 'Follow up', 'priority' => 'high', 'status' => 'pending']);
    $this->assertDatabaseHas('tasks', ['title' => 'Follow up', 'assigned_to' => $assignee->id, 'created_by' => 1]);

    // Ensure taskable relation exists on returned payload
    $response->assertJsonStructure(['taskable' => []]);
});

test('store returns 403 when user lacks permission', function () {
    $assignee = User::factory()->create();
    $creator = User::factory()->create();
    $taskable = Deal::factory()->create();

    $task = Task::factory()->create([
        'assigned_to' => $assignee->id,
        'created_by' => $creator->id,
        'taskable_type' => Deal::class,
        'taskable_id' => $taskable->id,
        'created_by' => $this->auth->id,
    ]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.tasks.store', $task->id));

    $response->assertStatus(403);
});

/**
 * --------------------------------------------------------------
 * --------------------------- Update ---------------------------
 * --------------------------------------------------------------
 */
test('update modifies an existing task and returns updated resource', function () {
    $assignee = User::factory()->create();
    $creator = User::factory()->create();
    $taskable = Deal::factory()->create();

    $task = Task::factory()->create([
        'title' => 'Old title',
        'assigned_to' => $assignee->id,
        'created_by' => $creator->id,
        'taskable_type' => Deal::class,
        'taskable_id' => $taskable->id,
        'priority' => 'low',
        'status' => 'pending',
        'created_by' => $this->auth->id,
    ]);

    $payload = [
        'title' => 'New title',
        'priority' => 'medium',
        'status' => 'completed',
    ];

    $response = $this->putJson(route('api.tasks.update', $task), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['title' => 'New title', 'priority' => 'medium', 'status' => 'completed']);
    $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'New title', 'status' => 'completed']);
});

test('update returns 403 when user lacks permission', function () {
    $assignee = User::factory()->create();
    $creator = User::factory()->create();
    $taskable = Deal::factory()->create();

    $task = Task::factory()->create([
        'assigned_to' => $assignee->id,
        'created_by' => $creator->id,
        'taskable_type' => Deal::class,
        'taskable_id' => $taskable->id,
        'created_by' => $this->auth->id,
    ]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.tasks.update', $task->id));

    $response->assertStatus(403);
});

test('update returns 404 for non-existent task', function () {
    $response = $this->putJson(route('api.tasks.update', 999999), ['name' => 'Ghost']);

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Destroy --------------------------
 * -------------------------------------------------------------
 */
test('destroy deletes the task', function () {
    $taskable = Deal::factory()->create();

    $task = Task::factory()->create([
        'taskable_type' => Deal::class,
        'taskable_id' => $taskable->id,
        'created_by' => $this->auth->id
    ]);

    $response = $this->deleteJson(route('api.tasks.destroy', $task));

    $response->assertStatus(204);

    $this->assertSoftDeleted('tasks', ['id' => $task->id]);
});


test('destroy returns 403 when user lacks permission', function () {
    $taskable = Deal::factory()->create();

    $task = Task::factory()->create([
        'taskable_type' => Deal::class,
        'taskable_id' => $taskable->id,
        'created_by' => $this->auth->id,
    ]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.tasks.destroy', $task->id));

    $response->assertStatus(403);
});

test('destroy returns 404 for non-existent task', function () {
    $response = $this->deleteJson(route('api.tasks.destroy', 999999));

    $response->assertStatus(404);
});
/**
 * -------------------------------------------------------------
 * -------------------------- Restore --------------------------
 * -------------------------------------------------------------
 */
test('restore deleted tasks', function () {
    $assignee = User::factory()->create();
    $creator = $this->auth;
    $taskable = Deal::factory()->create();

    $task = Task::factory()->create([
        'created_by' => $creator->id,
        'assigned_to' => $assignee->id,
        'taskable_type' => Deal::class,
        'taskable_id' => $taskable->id,
        'created_by' => $this->auth->id,
    ]);

    $task->delete();

    $this->assertSoftDeleted('tasks', ['id' => $task->id]);

    $response = $this->postJson(route('api.tasks.restore', $task->id));

    $response->assertStatus(200);

    $response->assertJsonFragment([
        'id' => $task->id,
    ]);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'deleted_at' => null,
    ]);

});

test('restore returns 403 when user lacks permission', function () {
    $assignee = User::factory()->create();
    $creator = $this->auth;
    $taskable = Deal::factory()->create();

    $task = Task::factory()->create([
        'created_by' => $creator->id,
        'assigned_to' => $assignee->id,
        'taskable_type' => Deal::class,
        'taskable_id' => $taskable->id,
        'created_by' => $this->auth->id,
    ]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.tasks.restore', $task->id));

    $response->assertStatus(403);
});

test('restore returns 404 for non-existent task', function () {
    $response = $this->postJson(route('api.tasks.restore', 999999));

    $response->assertStatus(404);
});

test('restore returns 404 when task is not deleted', function () {
    $assignee = User::factory()->create();
    $creator = $this->auth;
    $taskable = Deal::factory()->create();

    $task = Task::factory()->create([
        'created_by' => $creator->id,
        'assigned_to' => $assignee->id,
        'taskable_type' => Deal::class,
        'taskable_id' => $taskable->id,
        'created_by' => $this->auth->id,
    ]);

    $response = $this->postJson(route('api.tasks.restore', $task->id));

    $response->assertStatus(404);
});
