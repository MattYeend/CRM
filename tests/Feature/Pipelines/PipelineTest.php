<?php

use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'pipelines.view.all',
        'pipelines.create',
        'pipelines.update.any',
        'pipelines.delete.any',
        'pipelines.restore.any',
        'pipelineStages.view.all',
        'pipelineStages.create',
        'pipelineStages.update.any',
        'pipelineStages.delete.any',
        'pipelineSTages.restore.any',
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
test('index returns paginated pipelines with stages relation', function () {
    // create pipelines and attach stages so the relation is present
    Pipeline::factory()->count(8)->create()->each(function ($pipeline) {
        // create one stage for each pipeline so relation isn't empty
        PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);
    });

    $response = $this->getJson(route('api.pipelines.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);

    // Should return 5 items in data
    $this->assertCount(5, $response->json('data'));

    // Ensure the stages key is present on an item
    $this->assertArrayHasKey('stages', $response->json('data')[0]);
});

test('index returns all pipelines when no pagination specified', function () {
    Pipeline::factory()->count(3)->create();

    $response = $this->getJson(route('api.pipelines.index'));

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

    $response = $this->getJson(route('api.pipelines.index'));

    $response->assertStatus(403);
});

/**
 * -------------------------------------------------------------
 * ---------------------------- Show ----------------------------
 * -------------------------------------------------------------
 */
test('show returns a pipeline with stages loaded', function () {
    $pipeline = Pipeline::factory()->create();
    $stage = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);

    $response = $this->getJson(route('api.pipelines.show', $pipeline));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $pipeline->id]);
    $response->assertJsonStructure([
        'id',
        'name',
        'description',
        'is_default',
        'stages' => [],
    ]);
});

test('show returns 403 when user lacks permission', function () {
    $pipeline = Pipeline::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.pipelines.show', $pipeline->id));

    $response->assertStatus(403);
});

test('show returns 404 for non-existent pipeline', function () {
    $response = $this->getJson(route('api.pipelines.show', 999999));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Store ---------------------------
 * -------------------------------------------------------------
 */
test('store creates a new pipeline and returns 201', function () {
    $payload = [
        'name' => 'Sales Pipeline',
        'description' => 'Pipeline for outbound sales',
        'is_default' => false,
    ];

    $response = $this->postJson(route('api.pipelines.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['name' => 'Sales Pipeline', 'description' => 'Pipeline for outbound sales']);
    $this->assertDatabaseHas('pipelines', ['name' => 'Sales Pipeline']);
});

test('store returns 403 when user lacks permission', function () {
    $pipeline = Pipeline::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.pipelines.store', $pipeline->id));

    $response->assertStatus(403);
});

/**
 * --------------------------------------------------------------
 * --------------------------- Update ---------------------------
 * --------------------------------------------------------------
 */
test('update modifies an existing pipeline', function () {
    $pipeline = Pipeline::factory()->create(['name' => 'Old Name', 'description' => 'Old desc']);

    $payload = [
        'name' => 'New Pipeline Name',
        'description' => 'Updated description',
        'is_default' => true,
    ];

    $response = $this->putJson(route('api.pipelines.update', $pipeline), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['name' => 'New Pipeline Name', 'description' => 'Updated description']);
    $this->assertDatabaseHas('pipelines', ['id' => $pipeline->id, 'name' => 'New Pipeline Name']);
});

test('update returns 403 when user lacks permission', function () {
    $pipeline = Pipeline::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.pipelines.update', $pipeline->id));

    $response->assertStatus(403);
});

test('update returns 404 for non-existent pipeline', function () {
    $response = $this->putJson(route('api.pipelines.update', 999999), ['name' => 'Ghost']);

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Destroy --------------------------
 * -------------------------------------------------------------
 */
test('destroy deletes the pipeline', function () {
    $pipeline = Pipeline::factory()->create();

    $response = $this->deleteJson(route('api.pipelines.destroy', $pipeline));

    $response->assertStatus(204);
    $this->assertSoftDeleted('pipelines', ['id' => $pipeline->id]);
});

test('destroy returns 403 when user lacks permission', function () {
    $pipeline = Pipeline::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.pipelines.destroy', $pipeline->id));

    $response->assertStatus(403);
});

test('destroy returns 404 for non-existent pipeline', function () {
    $response = $this->deleteJson(route('api.pipelines.destroy', 999999));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Restore --------------------------
 * -------------------------------------------------------------
 */
test('restore deleted pipeline', function () {
    $pipeline = Pipeline::factory()->create(['created_by' => $this->auth->id]);
    $pipeline->delete();

    $this->assertSoftDeleted('pipelines', ['id' => $pipeline->id]);

    $response = $this->postJson(route('api.pipelines.restore', $pipeline->id));
    $response->assertStatus(200);

    $response->assertJsonFragment([
        'id' => $pipeline->id,
    ]);

    $this->assertDatabaseHas('pipelines', [
        'id' => $pipeline->id,
        'deleted_at' => null,
    ]);
});


test('restore returns 403 when user lacks permission', function () {
    $pipeline = Pipeline::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.pipelines.restore', $pipeline->id));

    $response->assertStatus(403);
});

test('restore returns 404 for non-existent pipeline', function () {
    $response = $this->postJson(route('api.pipelines.restore', 999999));

    $response->assertStatus(404);
});

test('restore returns 404 when pipeline is not deleted', function () {
    $pipeline = Pipeline::factory()->create();

    $response = $this->postJson(route('api.pipelines.restore', $pipeline->id));

    $response->assertStatus(404);
});
