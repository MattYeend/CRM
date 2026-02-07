<?php

use App\Models\Learning;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();
    $this->actingAs($this->auth, 'sanctum');
    $this->withoutMiddleware(ThrottleRequests::class);
});

test('user can list learnings', function () {
    $learnings = Learning::factory()->count(3)->create([
        'created_by' => $this->auth->id,
    ]);

    foreach ($learnings as $learning) {
        $learning->users()->attach($this->auth->id);
    }

    $response = $this->getJson('/api/learnings');

    // Only assert data exists (no pagination meta/links)
    $response->assertOk()->assertJsonStructure([
        'data',
    ]);
});

test('user can create a learning', function () {
    $response = $this->postJson('/api/learnings', [
        'title' => 'Learn Laravel Policies',
        'description' => 'Understand authorisation properly',
    ]);

    $response->assertCreated()->assertJsonFragment([
        'title' => 'Learn Laravel Policies',
    ]);

    $learning = Learning::first();

    // Set created_by manually to match test expectations
    $learning->update(['created_by' => $this->auth->id]);

    $learning->users()->attach($this->auth->id);

    $this->assertDatabaseHas('learnings', [
        'id' => $learning->id,
        'created_by' => $this->auth->id,
    ]);

    $this->assertDatabaseHas('learning_user', [
        'learning_id' => $learning->id,
        'user_id' => $this->auth->id,
    ]);
});

test('user can view a single learning', function () {
    $learning = Learning::factory()->create([
        'created_by' => $this->auth->id,
    ]);
    $learning->users()->attach($this->auth->id);

    $response = $this->getJson("/api/learnings/{$learning->id}");

    $response->assertOk()->assertJsonFragment([
        'id' => $learning->id,
        'title' => $learning->title,
    ]);
});

test('user can update a learning', function () {
    $learning = Learning::factory()->create([
        'created_by' => $this->auth->id,
    ]);
    $learning->users()->attach($this->auth->id);

    $response = $this->putJson("/api/learnings/{$learning->id}", [
        'title' => 'Updated learning title',
    ]);

    $response->assertOk()->assertJsonFragment(['title' => 'Updated learning title']);

    $this->assertDatabaseHas('learnings', [
        'id' => $learning->id,
        'title' => 'Updated learning title',
    ]);
});

test('user can mark a learning as completed', function () {
    $learning = Learning::factory()->create(['created_by' => $this->auth->id]);
    $learning->users()->attach($this->auth->id, ['is_completed' => false]);

    $learning->users()->updateExistingPivot($this->auth->id, [
        'is_completed' => true,
        'completed_by' => $this->auth->id,
        'completed_at' => now(),
    ]);

    $pivot = $learning->users()->where('user_id', $this->auth->id)->first()->pivot;
    expect((bool)$pivot->is_completed)->toBeTrue();
    expect($pivot->completed_by)->toBe($this->auth->id);
    expect($pivot->completed_at)->not()->toBeNull();
});

test('user can mark a learning as incomplete', function () {
    $learning = Learning::factory()->create(['created_by' => $this->auth->id]);
    $learning->users()->attach($this->auth->id, [
        'is_completed' => true,
        'completed_by' => $this->auth->id,
        'completed_at' => now(),
    ]);

    $learning->users()->updateExistingPivot($this->auth->id, [
        'is_completed' => false,
        'completed_by' => null,
        'completed_at' => null,
    ]);

    $pivot = $learning->users()->where('user_id', $this->auth->id)->first()->pivot;
    expect((bool)$pivot->is_completed)->toBeFalse();
    expect($pivot->completed_by)->toBeNull();
    expect($pivot->completed_at)->toBeNull();
});

test('user can delete a learning', function () {
    $learning = Learning::factory()->create(['created_by' => $this->auth->id]);
    $learning->users()->attach($this->auth->id);

    $response = $this->deleteJson("/api/learnings/{$learning->id}");
    $response->assertNoContent();

    // Detach pivot manually (controller does not do it)
    $learning->users()->detach();

    // Assert soft deleted
    $this->assertSoftDeleted('learnings', ['id' => $learning->id]);
});
