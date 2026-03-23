<?php

use App\Models\Permission;
use App\Models\Pipeline;
use App\Models\PipelineStage;
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
        'pipelineStages.restore.any',
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

test('index returns paginated pipeline stages with pipeline relation', function () {
    $pipeline = Pipeline::factory()->create();

    // create 12 stages for pagination, associated to the pipeline
    PipelineStage::factory()->count(12)->create(['pipeline_id' => $pipeline->id]);

    $response = $this->getJson(route('api.pipelineStages.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);

    // Should return 5 items in data
    $this->assertCount(5, $response->json('data'));

    // Ensure pipeline relation key exists on first item
    $this->assertArrayHasKey('pipeline', $response->json('data')[0]);
});

test('show returns a pipeline stage with pipeline loaded', function () {
    $pipeline = Pipeline::factory()->create();
    $stage = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);

    $response = $this->getJson(route('api.pipelineStages.show', $stage));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $stage->id, 'name' => $stage->name]);
    $response->assertJsonStructure([
        'id',
        'pipeline_id',
        'name',
        'position',
        'is_won_stage',
        'is_lost_stage',
        'pipeline' => [],
    ]);
});

test('store creates a new pipeline stage and returns 201', function () {
    $pipeline = Pipeline::factory()->create();

    $payload = [
        'pipeline_id' => $pipeline->id,
        'name' => 'Qualification',
        'position' => 10,
        'is_won_stage' => false,
        'is_lost_stage' => false,
    ];

    $response = $this->postJson(route('api.pipelineStages.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['name' => 'Qualification', 'position' => 10]);
    $this->assertDatabaseHas('pipeline_stages', ['name' => 'Qualification', 'pipeline_id' => $pipeline->id]);
});

test('update modifies an existing pipeline stage', function () {
    $pipeline = Pipeline::factory()->create();
    $stage = PipelineStage::factory()->create([
        'pipeline_id' => $pipeline->id,
        'name' => 'Old Stage',
        'position' => 1,
        'is_won_stage' => false,
    ]);

    $payload = [
        'name' => 'Won Stage',
        'position' => 2,
        'is_won_stage' => true,
    ];

    $response = $this->putJson(route('api.pipelineStages.update', $stage), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['name' => 'Won Stage', 'position' => 2, 'is_won_stage' => true]);
    $this->assertDatabaseHas('pipeline_stages', ['id' => $stage->id, 'name' => 'Won Stage']);
});

test('destroy deletes the pipeline stage', function () {
    $pipeline = Pipeline::factory()->create();
    $stage = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);

    $response = $this->deleteJson(route('api.pipelineStages.destroy', $stage));

    $response->assertStatus(204);

    $this->assertSoftDeleted('pipeline_stages', ['id' => $stage->id]);
});

test('restore deleted pipeline stage', function () {
    $pipelineStage = PipelineStage::factory()->create(['created_by' => $this->auth->id]);

    $pipelineStage->delete();

    $this->assertSoftDeleted('pipeline_stages', ['id' => $pipelineStage->id]);

    $response = $this->postJson(route('api.pipelineStages.restore', $pipelineStage->id));

    $response->assertStatus(200);

    $response->assertJsonFragment(['id' => $pipelineStage->id]);

    $this->assertDatabaseHas('pipeline_stages', [
        'id' => $pipelineStage->id,
        'deleted_at' => null,
    ]);
});
