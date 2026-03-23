<?php

use App\Models\Deal;
use App\Models\JobTitle;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a user and authenticate
    $this->auth = User::factory()->create();

    // Create permissions
    $permissions = [
        'jobTitles.view.all',
        'jobTitles.create',
        'jobTitles.update.any',
        'jobTitles.delete.any',
        'jobTitles.restore.any',
    ];

    $permissionModels = collect($permissions)
        ->map(fn($name) => Permission::firstOrCreate(['name' => $name]));

    // Create admin role and attach permissions
    $role = Role::factory()->create(['name' => 'admin']);
    $role->permissions()->sync($permissionModels->pluck('id'));

    // Attach role to user
    $this->auth->update([
        'role_id' => $role->id
    ]);

    // Authenticate user
    $this->actingAs($this->auth, 'sanctum');

    // Disable throttling
    $this->withoutMiddleware(ThrottleRequests::class);
});

test('index returns paginated job titles', function () {
    JobTitle::factory()->count(12)->create([
        'created_by' => $this->auth->id,
    ]);

    $response = $this->getJson(route('api.jobTitles.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('show returns a job title', function () {
    $jobTitle = JobTitle::factory()->create([
        'created_by' => $this->auth->id,
    ]);

    $response = $this->getJson(route('api.jobTitles.show', $jobTitle));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $jobTitle->id]);
    $response->assertJsonStructure([
        'id',
        'title',
        'short_code',
        'group',
        'meta',
        'creator',
        'updater',
    ]);
});

test('store creates a new job title', function () {
    $payload = [
        'title' => 'Test Title',
        'short_code' => 'TT1',
        'group' => 'Management',
        'meta' => ['priority' => 'high'],
        'created_by' => $this->auth->id,
    ];

    $response = $this->postJson(route('api.jobTitles.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['title' => 'Test Title']);
    $this->assertDatabaseHas('job_titles', ['title' => 'Test Title']);
});

test('update modifies an existing job title', function () {
    $jobTitle = JobTitle::factory()->create([
        'created_by' => $this->auth->id,
        'title' => 'Old Title',
        'short_code' => 'OLD',
    ]);

    $payload = [
        'title' => 'Updated Title',
        'short_code' => 'UPD',
        'group' => 'Executive',
        'meta' => ['priority' => 'low'],
        'updated_by' => $this->auth->id,
    ];

    $response = $this->putJson(route('api.jobTitles.update', $jobTitle), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['title' => 'Updated Title']);
    $this->assertDatabaseHas('job_titles', ['id' => $jobTitle->id, 'title' => 'Updated Title']);
});

test('destroy deletes a job title', function () {
    $jobTitle = JobTitle::factory()->create([
        'created_by' => $this->auth->id,
    ]);

    $response = $this->deleteJson(route('api.jobTitles.destroy', $jobTitle));

    $response->assertStatus(204);
    $this->assertSoftDeleted('job_titles', ['id' => $jobTitle->id]);
});

test('restore deleted job title', function () {
    $jobTitle = JobTitle::factory()->create([
        'created_by' => $this->auth->id,
    ]);

    $jobTitle->delete();

    $this->assertSoftDeleted('job_titles', ['id' => $jobTitle->id]);

    $response = $this->postJson(route('api.jobTitles.restore', $jobTitle->id));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $jobTitle->id]);
    $this->assertDatabaseHas('job_titles', [
        'id' => $jobTitle->id,
        'deleted_at' => null,
    ]);
});