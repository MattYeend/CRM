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
    ];

    // Create permissions in DB
    $permissionModels = collect($permissions)
        ->map(fn($name) => Permission::firstOrCreate(['name' => $name]));

    // Create admin role and attach permissions
    $role = Role::factory()->create(['name' => 'admin']);
    $role->permissions()->sync($permissionModels->pluck('id'));

    // Attach role to the user
    $this->auth->roles()->sync([$role->id]);

    // Authenticate the user
    $this->actingAs($this->auth, 'sanctum');

    // Disable throttling for tests
    $this->withoutMiddleware(ThrottleRequests::class);
});

test('index returns paginated notes with relations', function () {
    $notable = Deal::factory()->create();
    Note::factory()->count(12)->create([
        'user_id' => $this->auth->id,
        'notable_type' => 'deal',
        'notable_id' => $notable->id,
    ]);

    $response = $this->getJson(route('notes.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('show returns a note with user and notable loaded', function () {
    $notable = Deal::factory()->create();
    $note = Note::factory()->create([
        'user_id' => $this->auth->id,
        'notable_type' => 'deal',
        'notable_id' => $notable->id,
    ]);

    $response = $this->getJson(route('notes.show', $note));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $note->id]);
    $response->assertJsonStructure([
        'id',
        'user_id',
        'body',
        'meta',
        'user' => [],
        'notable' => [],
    ]);
});

test('store creates a new note and returns 201', function () {
    $notable = Deal::factory()->create();

    $payload = [
        'user_id' => $this->auth->id,
        'notable_type' => 'deal',
        'notable_id' => $notable->id,
        'body' => 'This is a test note',
        'meta' => ['priority' => 'high'],
    ];

    $response = $this->postJson(route('notes.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['body' => 'This is a test note']);
    $this->assertDatabaseHas('notes', ['body' => 'This is a test note']);
});

test('update modifies an existing note', function () {
    $notable = Deal::factory()->create();
    $note = Note::factory()->create([
        'user_id' => $this->auth->id,
        'notable_type' => 'deal',
        'notable_id' => $notable->id,
        'body' => 'Old note',
    ]);

    $payload = [
        'body' => 'Updated note',
        'meta' => ['priority' => 'low'],
    ];

    $response = $this->putJson(route('notes.update', $note), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['body' => 'Updated note']);
    $this->assertDatabaseHas('notes', ['id' => $note->id, 'body' => 'Updated note']);
});

test('destroy deletes a note', function () {
    $notable = Deal::factory()->create();
    $note = Note::factory()->create([
        'user_id' => $this->auth->id,
        'notable_type' => 'deal',
        'notable_id' => $notable->id,
    ]);

    $response = $this->deleteJson(route('notes.destroy', $note));

    $response->assertStatus(204);
    $this->assertSoftDeleted('notes', ['id' => $note->id]);
});

test('restore deleted note', function () {
    $notable = Deal::factory()->create();
    $note = Note::factory()->create([
        'user_id' => $this->auth->id,
        'notable_type' => 'deal',
        'notable_id' => $notable->id,
    ]);

    $note->delete();

    // Ensure it's soft deleted first
    $this->assertSoftDeleted('notes', ['id' => $note->id]);

    $response = $this->postJson(route('notes.restore', $note->id));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $note->id]);

    $this->assertDatabaseHas('notes', [
        'id' => $note->id,
        'deleted_at' => null,
    ]);
});