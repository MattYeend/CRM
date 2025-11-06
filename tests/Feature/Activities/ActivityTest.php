<?php

use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

/**
 * Disable the throttle middleware during tests to avoid:
 * "Rate limiter [App\Models\User::api] is not defined."
 */
beforeEach(function () {
    // Turn off the throttle middleware which is applied with 'throttle:api' in your routes
    $this->withoutMiddleware(ThrottleRequests::class);
});

test('index returns paginated activities and respects per_page query', function () {
    $auth = User::factory()->create();
    $this->actingAs($auth, 'sanctum');

    $subject = User::factory()->create();

    Activity::factory()->count(15)->create([
        'user_id' => $auth->id,
        'subject_type' => User::class,
        'subject_id' => $subject->id,
    ]);

    $response = $this->getJson(route('activities.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('show returns the activity with user and subject relationships loaded', function () {
    $auth = User::factory()->create();
    $this->actingAs($auth, 'sanctum');

    $user = User::factory()->create();
    $subject = User::factory()->create();

    $activity = Activity::factory()->create([
        'user_id' => $user->id,
        'subject_type' => User::class,
        'subject_id' => $subject->id,
        'type' => 'sample-type',
        'description' => 'Sample description',
    ]);

    $response = $this->getJson(route('activities.show', $activity));
    $response->assertStatus(200);

    $response->assertJsonFragment(['id' => $activity->id, 'type' => 'sample-type']);
    $response->assertJsonStructure([
        'id',
        'type',
        'description',
        'user' => ['id', 'name', 'email'],
        'subject' => ['id'],
    ]);
});

test('store creates an activity with valid payload and returns 201', function () {
    $auth = User::factory()->create();
    $this->actingAs($auth, 'sanctum');

    $user = User::factory()->create();

    $payload = [
        'user_id' => $user->id,
        'type' => 'created-thing',
        'subject_type' => User::class,
        'subject_id' => $user->id,
        'description' => 'Created an example thing',
        'meta' => ['ip' => '127.0.0.1'],
    ];

    $response = $this->postJson(route('activities.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['type' => 'created-thing', 'description' => 'Created an example thing']);

    $this->assertDatabaseHas('activities', [
        'user_id' => $user->id,
        'type' => 'created-thing',
        'description' => 'Created an example thing',
    ]);
});

test('store returns 422 when required fields are missing', function () {
    $auth = User::factory()->create();
    $this->actingAs($auth, 'sanctum');

    $payload = ['description' => 'no type provided'];

    $response = $this->postJson(route('activities.store'), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('type');
});

test('update modifies allowed fields and returns the updated activity', function () {
    $auth = User::factory()->create();
    $this->actingAs($auth, 'sanctum');

    $subject = User::factory()->create();

    $activity = Activity::factory()->create([
        'user_id' => $auth->id,
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

    $response = $this->patchJson(route('activities.update', $activity), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['type' => 'new-type', 'description' => 'new description']);

    $this->assertDatabaseHas('activities', [
        'id' => $activity->id,
        'type' => 'new-type',
        'description' => 'new description',
    ]);
});

test('destroy deletes the activity and returns 204', function () {
    $auth = User::factory()->create();
    $this->actingAs($auth, 'sanctum');

    $subject = User::factory()->create();

    $activity = Activity::factory()->create([
        'user_id' => $auth->id,
        'subject_type' => User::class,
        'subject_id' => $subject->id,
    ]);

    $response = $this->deleteJson(route('activities.destroy', $activity));

    $response->assertStatus(204);

    $this->assertDatabaseMissing('activities', [
        'id' => $activity->id,
    ]);
});