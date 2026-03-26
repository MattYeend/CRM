<?php

use App\Models\Part;
use App\Models\PartImage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'part_images.view.all',
        'part_images.create',
        'part_images.update.any',
        'part_images.delete.any',
        'part_images.restore.any',
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
test('index returns paginated part images', function () {
    PartImage::factory()->count(12)->create();

    $response = $this->getJson(route('api.partImages.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('index returns all part images when no pagination specified', function () {
    PartImage::factory()->count(3)->create();

    $response = $this->getJson(route('api.partImages.index'));

    $response->assertStatus(200);
    $this->assertCount(3, $response->json('data'));
});

test('index returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.partImages.index'));

    $response->assertStatus(403);
});

/**
 * ------------------------------------------------------------
 * --------------------------- Show ---------------------------
 * ------------------------------------------------------------
 */
test('show returns a single part image', function () {
    $partImage = PartImage::factory()->create();

    $response = $this->getJson(route('api.partImages.show', $partImage));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $partImage->id]);
    $response->assertJsonStructure(['id', 'part_id', 'url', 'order', 'is_primary']);
});

test('show returns 404 for non-existent part image', function () {
    $response = $this->getJson(route('api.partImages.show', 999999));

    $response->assertStatus(404);
});

test('show returns 403 when user lacks permission', function () {
    $partImage = PartImage::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.partImages.show', $partImage));

    $response->assertStatus(403);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Store ---------------------------
 * -------------------------------------------------------------
 */
test('store creates a new part image and returns 201', function () {
    $part = Part::factory()->create();

    $payload = [
        'part_id' => $part->id,
        'url' => 'https://example.com/images/part-image.jpg',
        'order' => 1,
        'is_primary' => true,
    ];

    $response = $this->postJson(route('api.partImages.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['part_id' => $part->id, 'is_primary' => true]);
    $this->assertDatabaseHas('part_images', ['part_id' => $part->id, 'url' => 'https://example.com/images/part-image.jpg']);
});

test('store returns 422 when required fields are missing', function () {
    $response = $this->postJson(route('api.partImages.store'), []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['part_id', 'url']);
});

test('store returns 422 when part_id does not exist', function () {
    $payload = [
        'part_id' => 999999,
        'url' => 'https://example.com/images/part-image.jpg',
    ];

    $response = $this->postJson(route('api.partImages.store'), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('part_id');
});

test('store returns 422 when url is not a valid url', function () {
    $part = Part::factory()->create();

    $payload = [
        'part_id' => $part->id,
        'url' => 'not-a-valid-url',
    ];

    $response = $this->postJson(route('api.partImages.store'), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('url');
});

test('store returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.partImages.store'), []);

    $response->assertStatus(403);
});

/**
 * --------------------------------------------------------------
 * --------------------------- Update ---------------------------
 * --------------------------------------------------------------
 */
test('update modifies an existing part image', function () {
    $partImage = PartImage::factory()->create(['is_primary' => false, 'order' => 1]);

    $payload = [
        'is_primary' => true,
        'order' => 2,
    ];

    $response = $this->putJson(route('api.partImages.update', $partImage), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['is_primary' => true, 'order' => 2]);
    $this->assertDatabaseHas('part_images', ['id' => $partImage->id, 'is_primary' => true, 'order' => 2]);
});

test('update allows saving without changing url', function () {
    $partImage = PartImage::factory()->create(['order' => 1]);

    $payload = ['order' => 3];

    $response = $this->putJson(route('api.partImages.update', $partImage), $payload);

    $response->assertStatus(200);
    $this->assertDatabaseHas('part_images', ['id' => $partImage->id, 'order' => 3]);
});

test('update returns 422 when url is invalid', function () {
    $partImage = PartImage::factory()->create();

    $response = $this->putJson(route('api.partImages.update', $partImage), [
        'url' => 'not-a-valid-url',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('url');
});

test('update returns 404 for non-existent part image', function () {
    $response = $this->putJson(route('api.partImages.update', 999999), ['order' => 1]);

    $response->assertStatus(404);
});

test('update returns 403 when user lacks permission', function () {
    $partImage = PartImage::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->putJson(route('api.partImages.update', $partImage), ['order' => 1]);

    $response->assertStatus(403);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Destroy --------------------------
 * -------------------------------------------------------------
 */
test('destroy soft deletes a part image and returns 204', function () {
    $partImage = PartImage::factory()->create();

    $response = $this->deleteJson(route('api.partImages.destroy', $partImage));

    $response->assertStatus(204);
    $this->assertSoftDeleted('part_images', ['id' => $partImage->id]);
});

test('destroy returns 404 for non-existent part image', function () {
    $response = $this->deleteJson(route('api.partImages.destroy', 999999));

    $response->assertStatus(404);
});

test('destroy returns 403 when user lacks permission', function () {
    $partImage = PartImage::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->deleteJson(route('api.partImages.destroy', $partImage));

    $response->assertStatus(403);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Restore --------------------------
 * -------------------------------------------------------------
 */
test('restore recovers a soft deleted part image', function () {
    $partImage = PartImage::factory()->create(['created_by' => $this->auth->id]);
    $partImage->delete();

    $this->assertSoftDeleted('part_images', ['id' => $partImage->id]);

    $response = $this->postJson(route('api.partImages.restore', $partImage->id));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $partImage->id]);
    $this->assertDatabaseHas('part_images', ['id' => $partImage->id, 'deleted_at' => null]);
});

test('restore returns 404 for non-existent part image', function () {
    $response = $this->postJson(route('api.partImages.restore', 999999));

    $response->assertStatus(404);
});

test('restore returns 403 when user lacks permission', function () {
    $partImage = PartImage::factory()->create();
    $partImage->delete();

    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.partImages.restore', $partImage->id));

    $response->assertStatus(403);
});

test('restore returns 404 when part image is not deleted', function () {
    $partImage = PartImage::factory()->create();

    $response = $this->postJson(route('api.partImages.restore', $partImage->id));

    $response->assertStatus(404);
});