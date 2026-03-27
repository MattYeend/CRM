<?php

use App\Models\Learning;
use App\Models\LearningAnswer;
use App\Models\LearningQuestion;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'learnings.view.all',
        'learnings.create',
        'learnings.update.any',
        'learnings.delete.any',
        'learnings.restore.any',
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
 * -----------------------------------------------------------
 * -------------------------- Index --------------------------
 * -----------------------------------------------------------
 */
test('user can list learnings', function () {
    $learnings = Learning::factory()->count(3)->create([
        'created_by' => $this->auth->id,
    ]);

    foreach ($learnings as $learning) {
        $learning->users()->attach($this->auth->id);
    }

    $response = $this->getJson('/api/learnings');

    $response->assertOk()->assertJsonStructure([
        'data',
    ]);
});

test('index returns all learnings when no pagination specified', function () {
    Learning::factory()->count(3)->create();

    $response = $this->getJson(route('api.learnings.index'));

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

    $response = $this->getJson(route('api.learnings.index'));

    $response->assertStatus(403);
});

/**
 * -----------------------------------------------------------
 * -------------------------- Show ---------------------------
 * -----------------------------------------------------------
 */
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

test('show returns 403 when user lacks permission', function () {
    $learning = Learning::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.learnings.show', $learning->id));

    $response->assertStatus(403);
});

test('show returns 404 for non-existent learning', function () {
    $response = $this->getJson(route('api.learnings.show', 999999));

    $response->assertStatus(404);
});

/**
 * -----------------------------------------------------------
 * -------------------------- Store --------------------------
 * -----------------------------------------------------------
 */
test('user can create a learning', function () {
    $response = $this->postJson('/api/learnings', [
        'title' => 'Learn Laravel Policies',
        'description' => 'Understand authorisation properly',
    ]);

    $response->assertCreated()->assertJsonFragment([
        'title' => 'Learn Laravel Policies',
    ]);

    $learning = Learning::first();

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

test('user can create a learning with questions and answers', function () {
    $response = $this->postJson('/api/learnings', [
        'title' => 'Learning with Questions',
        'questions' => [
            [
                'question' => 'What is Laravel?',
                'answers' => [
                    ['answer' => 'A PHP framework', 'is_correct' => true],
                    ['answer' => 'A JavaScript framework', 'is_correct' => false],
                ],
            ],
        ],
    ]);

    $response->assertCreated()->assertJsonFragment([
        'title' => 'Learning with Questions',
    ]);

    $learning = Learning::first();

    $this->assertDatabaseHas('learning_questions', [
        'learning_id' => $learning->id,
        'question' => 'What is Laravel?',
    ]);

    $question = $learning->questions()->first();

    $this->assertDatabaseHas('learning_answers', [
        'question_id' => $question->id,
        'answer' => 'A PHP framework',
        'is_correct' => true,
    ]);

    $this->assertDatabaseHas('learning_answers', [
        'question_id' => $question->id,
        'answer' => 'A JavaScript framework',
        'is_correct' => false,
    ]);
});

test('creating a learning question requires at least one correct answer', function () {
    $response = $this->postJson('/api/learnings', [
        'title' => 'Invalid Questions',
        'questions' => [
            [
                'question' => 'What is PHP?',
                'answers' => [
                    ['answer' => 'A language', 'is_correct' => false],
                    ['answer' => 'A framework', 'is_correct' => false],
                ],
            ],
        ],
    ]);

    $response->assertUnprocessable();
});

test('store returns 403 when user lacks permission', function () {
    $learning = Learning::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.learnings.store', $learning->id));

    $response->assertStatus(403);
});

/**
 * -----------------------------------------------------------
 * ------------------------- Update --------------------------
 * -----------------------------------------------------------
 */
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

test('user can update a learning with questions and answers', function () {
    $learning = Learning::factory()->create(['created_by' => $this->auth->id]);
    $learning->users()->attach($this->auth->id);

    $response = $this->putJson("/api/learnings/{$learning->id}", [
        'title' => 'Updated Learning',
        'questions' => [
            [
                'question' => 'What is Eloquent?',
                'answers' => [
                    ['answer' => 'An ORM', 'is_correct' => true],
                    ['answer' => 'A view engine', 'is_correct' => false],
                ],
            ],
        ],
    ]);

    $response->assertOk()->assertJsonFragment(['title' => 'Updated Learning']);

    $question = $learning->fresh()->questions()->first();

    $this->assertDatabaseHas('learning_questions', [
        'learning_id' => $learning->id,
        'question' => 'What is Eloquent?',
    ]);

    $this->assertDatabaseHas('learning_answers', [
        'question_id' => $question->id,
        'answer' => 'An ORM',
        'is_correct' => true,
    ]);
});

test('updating a learning replaces existing questions and answers', function () {
    $learning = Learning::factory()
        ->has(
            LearningQuestion::factory()
                ->has(LearningAnswer::factory()->correct(), 'answers')
                ->has(LearningAnswer::factory()->count(2), 'answers'),
            'questions'
        )
        ->create(['created_by' => $this->auth->id]);

    $learning->users()->attach($this->auth->id);

    $oldQuestion = $learning->questions()->first();

    $this->putJson("/api/learnings/{$learning->id}", [
        'questions' => [
            [
                'question' => 'A brand new question?',
                'answers' => [
                    ['answer' => 'New correct answer', 'is_correct' => true],
                ],
            ],
        ],
    ])->assertOk();

    $this->assertDatabaseMissing('learning_questions', [
        'id' => $oldQuestion->id,
    ]);

    $this->assertDatabaseHas('learning_questions', [
        'learning_id' => $learning->id,
        'question' => 'A brand new question?',
    ]);
});

test('update returns 403 when user lacks permission', function () {
    $learning = Learning::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.learnings.update', $learning->id));

    $response->assertStatus(403);
});

test('update returns 404 for non-existent learning', function () {
    $response = $this->putJson(route('api.learnings.update', 999999), ['name' => 'Ghost']);

    $response->assertStatus(404);
});

/**
 * -----------------------------------------------------------
 * ----------------------- Completion ------------------------
 * -----------------------------------------------------------
 */
test('user can mark a learning as completed', function () {
    $learning = Learning::factory()->create(['created_by' => $this->auth->id]);
    $learning->users()->attach($this->auth->id, ['is_complete' => false]);

    $learning->users()->updateExistingPivot($this->auth->id, [
        'is_complete' => true,
        'completed_at' => now(),
    ]);

    $pivot = $learning->users()->where('user_id', $this->auth->id)->first()->pivot;
    expect((bool) $pivot->is_complete)->toBeTrue();
    expect($pivot->completed_at)->not()->toBeNull();
});

test('user can mark a learning as incomplete', function () {
    $learning = Learning::factory()->create(['created_by' => $this->auth->id]);
    $learning->users()->attach($this->auth->id, [
        'is_complete' => true,
        'completed_at' => now(),
    ]);

    $learning->users()->updateExistingPivot($this->auth->id, [
        'is_complete' => false,
        'completed_at' => null,
    ]);

    $pivot = $learning->users()->where('user_id', $this->auth->id)->first()->pivot;
    expect((bool) $pivot->is_complete)->toBeFalse();
    expect($pivot->completed_at)->toBeNull();
});

/**
 * -----------------------------------------------------------
 * ------------------------- Destroy -------------------------
 * -----------------------------------------------------------
 */
test('user can delete a learning', function () {
    $learning = Learning::factory()->create(['created_by' => $this->auth->id]);
    $learning->users()->attach($this->auth->id);

    $response = $this->deleteJson("/api/learnings/{$learning->id}");
    $response->assertNoContent();

    $learning->users()->detach();

    $this->assertSoftDeleted('learnings', ['id' => $learning->id]);
});

test('deleting a learning also deletes its questions and answers', function () {
    $learning = Learning::factory()
        ->has(
            LearningQuestion::factory()
                ->has(LearningAnswer::factory()->correct(), 'answers'),
            'questions'
        )
        ->create(['created_by' => $this->auth->id]);

    $learning->users()->attach($this->auth->id);

    $question = $learning->questions()->first();
    $answer = $question->answers()->first();

    $this->deleteJson("/api/learnings/{$learning->id}")->assertNoContent();

    $this->assertSoftDeleted('learnings', ['id' => $learning->id]);

    $this->assertDatabaseMissing('learning_questions', ['id' => $question->id]);
    $this->assertDatabaseMissing('learning_answers', ['id' => $answer->id]);
});

test('destroy returns 403 when user lacks permission', function () {
    $learning = Learning::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.learnings.destroy', $learning->id));

    $response->assertStatus(403);
});

test('destroy returns 404 for non-existent learning', function () {
    $response = $this->deleteJson(route('api.learnings.destroy', 999999));

    $response->assertStatus(404);
});

/**
 * -----------------------------------------------------------
 * ------------------------- Restore -------------------------
 * -----------------------------------------------------------
 */
test('restore deleted learning', function () {
    $learning = Learning::factory()->create(['created_by' => $this->auth->id]);
    $learning->delete();

    $this->assertSoftDeleted('learnings', ['id' => $learning->id]);

    $response = $this->postJson(route('api.learnings.restore', $learning->id));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $learning->id]);

    $this->assertDatabaseHas('learnings', [
        'id' => $learning->id,
        'deleted_at' => null,
    ]);
});

test('restore returns 403 when user lacks permission', function () {
    $learning = Learning::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.learnings.restore', $learning->id));

    $response->assertStatus(403);
});

test('restore returns 404 for non-existent learning', function () {
    $response = $this->postJson(route('api.learnings.restore', 999999));

    $response->assertStatus(404);
});

test('restore returns 404 when learning is not deleted', function () {
    $learning = Learning::factory()->create();

    $response = $this->postJson(route('api.learnings.restore', $learning->id));

    $response->assertStatus(404);
});
