<?php

use App\Models\PartCategory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'partCategories.view.all',
        'partCategories.create',
        'partCategories.update.any',
        'partCategories.delete.any',
        'partCategories.restore.any',
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
test('index returns paginated part categories', function () {
    PartCategory::factory()->count(12)->create();

    $response = $this->getJson(route('api.partCategories.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('index returns all part categories when no pagination specified', function () {
    PartCategory::factory()->count(3)->create();

    $response = $this->getJson(route('api.partCategories.index'));

    $response->assertStatus(200);
    $this->assertCount(3, $response->json('data'));
});

test('index returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.partCategories.index'));

    $response->assertStatus(403);
});

/**
 * --------------------------------------------------------------
 * ---------------------------- Show ----------------------------
 * --------------------------------------------------------------
 */
test('show returns a single part category', function () {
    $category = PartCategory::factory()->create();

    $response = $this->getJson(route('api.partCategories.show', $category));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $category->id, 'name' => $category->name]);
    $response->assertJsonStructure(['id', 'name', 'slug', 'description', 'is_test', 'parent_id']);
});

test('show returns 404 for non-existent part category', function () {
    $response = $this->getJson(route('api.partCategories.show', 999999));

    $response->assertStatus(404);
});

test('show returns 403 when user lacks permission', function () {
    $category = PartCategory::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.partCategories.show', $category));

    $response->assertStatus(403);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Store ---------------------------
 * -------------------------------------------------------------
 */
test('store creates a new part category and returns 201', function () {
    $payload = [
        'name' => 'Engine Parts',
        'slug' => 'engine-parts',
        'description' => 'All parts related to the engine',
        'is_test' => false,
    ];

    $response = $this->postJson(route('api.partCategories.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['name' => 'Engine Parts', 'slug' => 'engine-parts']);
    $this->assertDatabaseHas('part_categories', ['name' => 'Engine Parts', 'slug' => 'engine-parts']);
});

test('store creates a child part category under a parent', function () {
    $parent = PartCategory::factory()->create();

    $payload = [
        'parent_id' => $parent->id,
        'name' => 'Child Category',
        'slug' => 'child-category',
    ];

    $response = $this->postJson(route('api.partCategories.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['parent_id' => $parent->id]);
    $this->assertDatabaseHas('part_categories', ['parent_id' => $parent->id, 'slug' => 'child-category']);
});

test('store returns 422 when required fields are missing', function () {
    $response = $this->postJson(route('api.partCategories.store'), []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name', 'slug']);
});

test('store returns 422 when name is not unique', function () {
    PartCategory::factory()->create(['name' => 'Duplicate Name']);

    $payload = [
        'name' => 'Duplicate Name',
        'slug' => 'some-other-slug',
    ];

    $response = $this->postJson(route('api.partCategories.store'), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('name');
});

test('store returns 422 when slug is not unique', function () {
    PartCategory::factory()->create(['slug' => 'duplicate-slug']);

    $payload = [
        'name' => 'Some Other Name',
        'slug' => 'duplicate-slug',
    ];

    $response = $this->postJson(route('api.partCategories.store'), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('slug');
});

test('store returns 422 when slug contains invalid characters', function () {
    $payload = [
        'name' => 'Bad Slug Category',
        'slug' => 'invalid slug with spaces!',
    ];

    $response = $this->postJson(route('api.partCategories.store'), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('slug');
});

test('store returns 422 when parent_id does not exist', function () {
    $payload = [
        'name' => 'Orphan Category',
        'slug' => 'orphan-category',
        'parent_id' => 999999,
    ];

    $response = $this->postJson(route('api.partCategories.store'), $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('parent_id');
});

test('store returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.partCategories.store'), []);

    $response->assertStatus(403);
});

/**
 * ------------------------------------------------------------
 * -------------------------- Update --------------------------
 * ------------------------------------------------------------
 */
test('update modifies an existing part category', function () {
    $category = PartCategory::factory()->create(['name' => 'Old Name']);

    $payload = [
        'name' => 'Updated Name',
        'description' => 'Updated description',
    ];

    $response = $this->putJson(route('api.partCategories.update', $category), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['name' => 'Updated Name']);
    $this->assertDatabaseHas('part_categories', ['id' => $category->id, 'name' => 'Updated Name']);
});

test('update allows saving without changing name or slug', function () {
    $category = PartCategory::factory()->create(['name' => 'Stable Name', 'slug' => 'stable-name']);

    $payload = ['description' => 'Just updating the description'];

    $response = $this->putJson(route('api.partCategories.update', $category), $payload);

    $response->assertStatus(200);
    $this->assertDatabaseHas('part_categories', ['id' => $category->id, 'name' => 'Stable Name']);
});

test('update returns 422 when name conflicts with another category', function () {
    PartCategory::factory()->create(['name' => 'Taken Name']);
    $category = PartCategory::factory()->create(['name' => 'My Category']);

    $response = $this->putJson(route('api.partCategories.update', $category), [
        'name' => 'Taken Name',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('name');
});

test('update returns 422 when parent_id is set to itself', function () {
    $category = PartCategory::factory()->create();

    $response = $this->putJson(route('api.partCategories.update', $category), [
        'parent_id' => $category->id,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('parent_id');
});

test('update returns 404 for non-existent part category', function () {
    $response = $this->putJson(route('api.partCategories.update', 999999), ['name' => 'Ghost']);

    $response->assertStatus(404);
});

test('update returns 403 when user lacks permission', function () {
    $category = PartCategory::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->putJson(route('api.partCategories.update', $category), ['name' => 'Sneaky']);

    $response->assertStatus(403);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Destroy --------------------------
 * -------------------------------------------------------------
 */
test('destroy soft deletes a part category and returns 204', function () {
    $category = PartCategory::factory()->create();

    $response = $this->deleteJson(route('api.partCategories.destroy', $category));

    $response->assertStatus(204);
    $this->assertSoftDeleted('part_categories', ['id' => $category->id]);
});

test('destroy returns 404 for non-existent part category', function () {
    $response = $this->deleteJson(route('api.partCategories.destroy', 999999));

    $response->assertStatus(404);
});

test('destroy returns 403 when user lacks permission', function () {
    $category = PartCategory::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->deleteJson(route('api.partCategories.destroy', $category));

    $response->assertStatus(403);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Restore --------------------------
 * -------------------------------------------------------------
 */
test('restore recovers a soft deleted part category', function () {
    $category = PartCategory::factory()->create(['created_by' => $this->auth->id]);
    $category->delete();

    $this->assertSoftDeleted('part_categories', ['id' => $category->id]);

    $response = $this->postJson(route('api.partCategories.restore', $category->id));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $category->id]);
    $this->assertDatabaseHas('part_categories', ['id' => $category->id, 'deleted_at' => null]);
});

test('restore returns 404 for non-existent part category', function () {
    $response = $this->postJson(route('api.partCategories.restore', 999999));

    $response->assertStatus(404);
});

test('restore returns 403 when user lacks permission', function () {
    $category = PartCategory::factory()->create();
    $category->delete();

    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.partCategories.restore', $category->id));

    $response->assertStatus(403);
});

test('restore returns 404 when part category is not deleted', function () {
    $category = PartCategory::factory()->create();

    $response = $this->postJson(route('api.partCategories.restore', $category->id));

    $response->assertStatus(404);
});