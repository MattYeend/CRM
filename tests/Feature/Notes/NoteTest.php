<?php

use App\Models\Note;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Deal;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'notes.view.all',
        'notes.create',
        'notes.update.any',
        'notes.delete.any',
        'notes.restore.any',
        'notes.access.any',
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
test('index returns paginated notes with relations', function () {
    $notable = Deal::factory()->create();
    Note::factory()->count(12)->create([
        'user_id' => $this->auth->id,
        'notable_type' => Deal::class,
        'notable_id' => $notable->id,
    ]);

    $response = $this->getJson(route('api.notes.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('index returns all notes when no pagination specified', function () {
    Note::factory()->count(3)->create();

    $response = $this->getJson(route('api.notes.index'));

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

    $response = $this->getJson(route('api.notes.index'));

    $response->assertStatus(403);
});

/**
 * --------------------------------------------------------------
 * ---------------------------- Show ----------------------------
 * --------------------------------------------------------------
 */
test('show returns a note with user and notable loaded', function () {
    $notable = Deal::factory()->create();
    $note = Note::factory()->create([
        'user_id' => $this->auth->id,
        'notable_type' => Deal::class,
        'notable_id' => $notable->id,
    ]);

    $response = $this->getJson(route('api.notes.show', $note));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $note->id]);
    $response->assertJsonStructure([
        'id',
        'user',
        'body',
        'meta',
        'user' => [],
        'notable' => [],
    ]);
});

test('show returns 403 when user lacks permission', function () {
    $note = Note::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.notes.show', $note->id));

    $response->assertStatus(403);
});

test('show returns 404 for non-existent note', function () {
    $response = $this->getJson(route('api.notes.show', 999999));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Store ---------------------------
 * -------------------------------------------------------------
 */
test('store creates a new note and returns 201', function () {
    $notable = Deal::factory()->create();

    $payload = [
        'user_id' => $this->auth->id,
        'notable_type' => 'deal',
        'notable_id' => $notable->id,
        'body' => 'This is a test note',
        'meta' => ['priority' => 'high'],
    ];

    $response = $this->postJson(route('api.notes.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['body' => 'This is a test note']);
    $this->assertDatabaseHas('notes', ['body' => 'This is a test note']);
});

test('store returns 403 when user lacks permission', function () {
    $note = Note::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.notes.store', $note->id));

    $response->assertStatus(403);
});

/**
 * --------------------------------------------------------------
 * --------------------------- Update ---------------------------
 * --------------------------------------------------------------
 */
test('update modifies an existing note', function () {
    $notable = Deal::factory()->create();
    $note = Note::factory()->create([
        'user_id' => $this->auth->id,
        'notable_type' => Deal::class,
        'notable_id' => $notable->id,
        'body' => 'Old note',
    ]);

    $payload = [
        'body' => 'Updated note',
        'meta' => ['priority' => 'low'],
    ];

    $response = $this->putJson(route('api.notes.update', $note), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['body' => 'Updated note']);
    $this->assertDatabaseHas('notes', ['id' => $note->id, 'body' => 'Updated note']);
});

test('update returns 403 when user lacks permission', function () {
    $note = Note::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.notes.update', $note->id));

    $response->assertStatus(403);
});

test('update returns 404 for non-existent note', function () {
    $response = $this->putJson(route('api.notes.update', 999999), ['name' => 'Ghost']);

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Destroy --------------------------
 * -------------------------------------------------------------
 */
test('destroy deletes a note', function () {
    $notable = Deal::factory()->create();
    $note = Note::factory()->create([
        'user_id' => $this->auth->id,
        'notable_type' => Deal::class,
        'notable_id' => $notable->id,
    ]);

    $response = $this->deleteJson(route('api.notes.destroy', $note));

    $response->assertStatus(204);
    $this->assertSoftDeleted('notes', ['id' => $note->id]);
});

test('destroy returns 403 when user lacks permission', function () {
    $note = Note::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.notes.destroy', $note->id));

    $response->assertStatus(403);
});

test('destroy returns 404 for non-existent note', function () {
    $response = $this->deleteJson(route('api.notes.destroy', 999999));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Restore --------------------------
 * -------------------------------------------------------------
 */
test('restore deleted note', function () {
    $notable = Deal::factory()->create();
    $note = Note::factory()->create([
        'user_id' => $this->auth->id,
        'notable_type' => Deal::class,
        'notable_id' => $notable->id,
    ]);

    $note->delete();

    $this->assertSoftDeleted('notes', ['id' => $note->id]);

    $response = $this->postJson(route('api.notes.restore', $note->id));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $note->id]);

    $this->assertDatabaseHas('notes', [
        'id' => $note->id,
        'deleted_at' => null,
    ]);
});

test('restore returns 403 when user lacks permission', function () {
    $note = Note::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.notes.restore', $note->id));

    $response->assertStatus(403);
});

test('restore returns 404 for non-existent note', function () {
    $response = $this->postJson(route('api.notes.restore', 999999));

    $response->assertStatus(404);
});

test('restore returns 404 when note is not deleted', function () {
    $note = Note::factory()->create();

    $response = $this->postJson(route('api.notes.restore', $note->id));

    $response->assertStatus(404);
});
