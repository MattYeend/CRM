<?php

use App\Models\Task;
use App\Models\User;
use App\Models\Deal; // example polymorphic target
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Authenticate requests and disable throttle middleware applied with 'throttle:api'
    $this->auth = User::factory()->create();
    $this->actingAs($this->auth, 'sanctum');
    $this->withoutMiddleware(ThrottleRequests::class);
});

test('index returns paginated tasks with relations', function () {
    $assignee = User::factory()->create();
    $creator = User::factory()->create();
    $taskable = Deal::factory()->create();

    Task::factory()->count(12)->create([
        'assigned_to' => $assignee->id,
        'created_by' => $creator->id,
        'taskable_type' => Deal::class,
        'taskable_id' => $taskable->id,
    ]);

    $response = $this->getJson(route('tasks.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));

    // ensure relations are present on first item
    $first = $response->json('data')[0];
    $this->assertArrayHasKey('assignee', $first);
    $this->assertArrayHasKey('creator', $first);
    $this->assertArrayHasKey('taskable', $first);
});

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

    $response = $this->getJson(route('tasks.show', $task));

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

    $response = $this->postJson(route('tasks.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['title' => 'Follow up', 'priority' => 'high', 'status' => 'pending']);
    $this->assertDatabaseHas('tasks', ['title' => 'Follow up', 'assigned_to' => $assignee->id, 'created_by' => $creator->id]);

    // Ensure taskable relation exists on returned payload
    $response->assertJsonStructure(['taskable' => []]);
});

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
    ]);

    $payload = [
        'title' => 'New title',
        'priority' => 'medium',
        'status' => 'completed',
    ];

    $response = $this->putJson(route('tasks.update', $task), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['title' => 'New title', 'priority' => 'medium', 'status' => 'completed']);
    $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'New title', 'status' => 'completed']);
});

test('destroy deletes the task', function () {
    $taskable = Deal::factory()->create();

    $task = Task::factory()->create([
        'taskable_type' => Deal::class,
        'taskable_id' => $taskable->id,
    ]);

    $response = $this->deleteJson(route('tasks.destroy', $task));

    $response->assertStatus(204);

    $this->assertSoftDeleted('tasks', ['id' => $task->id]);
});