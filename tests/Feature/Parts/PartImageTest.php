<?php

use App\Models\Part;
use App\Models\PartImage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Http\UploadedFile;


uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'partImages.view.all',
        'partImages.create',
        'partImages.update.any',
        'partImages.delete.any',
        'partImages.restore.any',
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
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

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
    $response->assertJsonStructure(['id', 'part_id', 'image', 'sort_order', 'is_primary']);
});

test('show returns 403 when user lacks permission', function () {
    $part = PartImage::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.partImages.show', $part->id));

    $response->assertStatus(403);
});

test('show returns 404 for non-existent part image', function () {
    $response = $this->getJson(route('api.partImages.show', 999999));

    $response->assertStatus(404);
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
        'image' => UploadedFile::fake()->image('part-image.jpg'),
        'sort_order' => 1,
        'is_primary' => true,
    ];

    $response = $this->postJson(route('api.partImages.store'), $payload);

    $response->assertStatus(201);

    $imagePath = $response->json('image');

    $response->assertJsonFragment(['part_id' => $part->id, 'is_primary' => true]);
    $this->assertDatabaseHas(
        'part_images',
        [
            'part_id' => $part->id,
            'image' => $imagePath,
        ]
    );
});

test('store returns 403 when user lacks permission', function () {
    $part = PartImage::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.partImages.store', $part->id));

    $response->assertStatus(403);
});

test('store returns 422 when required fields are missing', function () {
    $response = $this->postJson(route('api.partImages.store'), []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['part_id', 'image']);
});

test('store returns 422 when part_id does not exist', function () {
    $payload = [
        'part_id' => 999999,
        'image' => UploadedFile::fake()->image('part-image.jpg'),
    ];

    $response = $this->postJson(route('api.partImages.store'), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('part_id');
});

test('store returns 422 when url is not a valid url', function () {
    $part = Part::factory()->create();

    $payload = [
        'part_id' => $part->id,
        'image' => 'not-a-valid-url',
    ];

    $response = $this->postJson(route('api.partImages.store'), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('image');
});

/**
 * --------------------------------------------------------------
 * --------------------------- Update ---------------------------
 * --------------------------------------------------------------
 */
test('update modifies an existing partImage image', function () {
    $partImage = PartImage::factory()->create(['is_primary' => false, 'sort_order' => 1]);

    $payload = [
        'is_primary' => true,
        'sort_order' => 2,
    ];

    $response = $this->putJson(route('api.partImages.update', $partImage), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['is_primary' => true, 'sort_order' => 2]);
    $this->assertDatabaseHas('part_images', ['id' => $partImage->id, 'is_primary' => true, 'sort_order' => 2]);
});

test('update allows saving without changing url', function () {
    $partImage = PartImage::factory()->create(['sort_order' => 1]);

    $payload = ['sort_order' => 3];

    $response = $this->putJson(route('api.partImages.update', $partImage), $payload);

    $response->assertStatus(200);
    $this->assertDatabaseHas('part_images', ['id' => $partImage->id, 'sort_order' => 3]);
});

test('update returns 403 when user lacks permission', function () {
    $partImage = PartImage::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.partImages.update', $partImage->id));

    $response->assertStatus(403);
});

test('update returns 404 for non-existent partImage image', function () {
    $response = $this->putJson(route('api.partImages.update', 999999), ['sort_order' => 1]);

    $response->assertStatus(404);
});

test('update returns 422 when url is invalid', function () {
    $partImage = PartImage::factory()->create();

    $response = $this->putJson(route('api.partImages.update', $partImage), [
        'image' => 'not-a-valid-url',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('image');
});

/**
 * -------------------------------------------------------------
 * -------------------------- Destroy --------------------------
 * -------------------------------------------------------------
 */
test('destroy soft deletes a partImage image and returns 204', function () {
    $partImage = PartImage::factory()->create();

    $response = $this->deleteJson(route('api.partImages.destroy', $partImage));

    $response->assertStatus(204);
    $this->assertSoftDeleted('part_images', ['id' => $partImage->id]);
});

test('destroy returns 403 when user lacks permission', function () {
    $partImage = PartImage::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.partImages.destroy', $partImage->id));

    $response->assertStatus(403);
});

test('destroy returns 404 for non-existent partImage image', function () {
    $response = $this->deleteJson(route('api.partImages.destroy', 999999));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Restore --------------------------
 * -------------------------------------------------------------
 */
test('restore recovers a soft deleted partImage image', function () {
    $partImage = PartImage::factory()->create(['created_by' => $this->auth->id]);
    $partImage->delete();

    $this->assertSoftDeleted('part_images', ['id' => $partImage->id]);

    $response = $this->postJson(route('api.partImages.restore', $partImage->id));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $partImage->id]);
    $this->assertDatabaseHas('part_images', ['id' => $partImage->id, 'deleted_at' => null]);
});

test('restore returns 403 when user lacks permission', function () {
    $partImage = PartImage::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.partImages.restore', $partImage->id));

    $response->assertStatus(403);
});

test('restore returns 404 for non-existent part image', function () {
    $response = $this->postJson(route('api.partImages.restore', 999999));

    $response->assertStatus(404);
});

test('restore returns 404 when part image is not deleted', function () {
    $partImage = PartImage::factory()->create();

    $response = $this->postJson(route('api.partImages.restore', $partImage->id));

    $response->assertStatus(404);
});
