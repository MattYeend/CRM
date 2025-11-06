<?php

use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Authenticated user for API requests
    $this->auth = User::factory()->create();
    $this->actingAs($this->auth, 'sanctum');

    // Disable throttle middleware (routes use throttle:api)
    $this->withoutMiddleware(ThrottleRequests::class);
});

test('index returns paginated pipelines with stages relation', function () {
    // create pipelines and attach stages so the relation is present
    Pipeline::factory()->count(8)->create()->each(function ($pipeline) {
        // create one stage for each pipeline so relation isn't empty
        PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);
    });

    $response = $this->getJson(route('pipelines.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);

    // Should return 5 items in data
    $this->assertCount(5, $response->json('data'));

    // Ensure the stages key is present on an item
    $this->assertArrayHasKey('stages', $response->json('data')[0]);
});

test('show returns a pipeline with stages loaded', function () {
    $pipeline = Pipeline::factory()->create();
    $stage = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);

    $response = $this->getJson(route('pipelines.show', $pipeline));

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

test('store creates a new pipeline and returns 201', function () {
    $payload = [
        'name' => 'Sales Pipeline',
        'description' => 'Pipeline for outbound sales',
        'is_default' => false,
    ];

    $response = $this->postJson(route('pipelines.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['name' => 'Sales Pipeline', 'description' => 'Pipeline for outbound sales']);
    $this->assertDatabaseHas('pipelines', ['name' => 'Sales Pipeline']);
});

test('update modifies an existing pipeline', function () {
    $pipeline = Pipeline::factory()->create(['name' => 'Old Name', 'description' => 'Old desc']);

    $payload = [
        'name' => 'New Pipeline Name',
        'description' => 'Updated description',
        'is_default' => true,
    ];

    $response = $this->putJson(route('pipelines.update', $pipeline), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['name' => 'New Pipeline Name', 'description' => 'Updated description']);
    $this->assertDatabaseHas('pipelines', ['id' => $pipeline->id, 'name' => 'New Pipeline Name']);
});

test('destroy deletes the pipeline', function () {
    $pipeline = Pipeline::factory()->create();

    $response = $this->deleteJson(route('pipelines.destroy', $pipeline));

    $response->assertStatus(204);
    $this->assertDatabaseMissing('pipelines', ['id' => $pipeline->id]);
});