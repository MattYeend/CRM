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
