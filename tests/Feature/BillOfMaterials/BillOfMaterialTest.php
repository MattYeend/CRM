<?php

use App\Models\Part;
use App\Models\BillOfMaterial;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'billOfMaterials.view.all',
        'billOfMaterials.create',
        'billOfMaterials.update.any',
        'billOfMaterials.delete.any',
        'billOfMaterials.restore.any',
        'billOfMaterials.access.any',
    ];

    $permissionModels = collect($permissions)
        ->map(fn ($name) => Permission::firstOrCreate(['name' => $name]));

    $role = Role::factory()->create(['name' => 'admin']);

    $role->permissions()->sync($permissionModels->pluck('id'));

    $this->auth->update([
        'role_id' => $role->id,
    ]);

    $this->actingAs($this->auth, 'sanctum');

    $this->withoutMiddleware(ThrottleRequests::class);

    $this->part = Part::factory()->create(['is_manufactured' => true]);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Index ---------------------------
 * -------------------------------------------------------------
 */
test('index returns paginated bill of materials for a part', function () {
    BillOfMaterial::factory()->count(12)->create(['parent_part_id' => $this->part->id]);

    $response = $this->getJson(route('api.billOfMaterials.index', [
        'part' => $this->part,
        'per_page' => 5,
    ]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('index returns all bill of materials when no pagination specified', function () {
    BillOfMaterial::factory()->count(3)->create(['parent_part_id' => $this->part->id]);

    $response = $this->getJson(route('api.billOfMaterials.index', [
        'part' => $this->part,
    ]));

    $response->assertStatus(200);
    $this->assertCount(3, $response->json('data'));
});

test('index only returns bill of materials scoped to the given part', function () {
    $otherPart = Part::factory()->create(['is_manufactured' => true]);

    BillOfMaterial::factory()->count(2)->create(['parent_part_id' => $this->part->id]);
    BillOfMaterial::factory()->count(3)->create(['parent_part_id' => $otherPart->id]);

    $response = $this->getJson(route('api.billOfMaterials.index', [
        'part' => $this->part,
    ]));

    $response->assertStatus(200);
    $this->assertCount(2, $response->json('data'));
});

test('index returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update(['role_id' => $role->id]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.billOfMaterials.index', [
        'part' => $this->part,
    ]));

    $response->assertStatus(403);
});

test('index returns 404 for non-existent part', function () {
    $response = $this->getJson(route('api.billOfMaterials.index', [
        'part' => 999999,
    ]));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Store ---------------------------
 * -------------------------------------------------------------
 */
test('store creates a new bill of material and returns 201', function () {
    $childPart = Part::factory()->create();

    $payload = [
        'child_part_id' => $childPart->id,
        'quantity' => 3,
        'unit_of_measure' => 'each',
        'notes' => 'Test BOM entry',
    ];

    $response = $this->postJson(route('api.billOfMaterials.store', [
        'part' => $this->part,
    ]), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment([
        'child_part_id' => $childPart->id,
        'parent_part_id' => $this->part->id,
    ]);
    $this->assertDatabaseHas('bill_of_materials', [
        'parent_part_id' => $this->part->id,
        'child_part_id' => $childPart->id,
    ]);
});

test('store returns 422 when part is not manufactured', function () {
    $nonManufacturedPart = Part::factory()->create(['is_manufactured' => false]);
    $childPart = Part::factory()->create();

    $payload = [
        'child_part_id' => $childPart->id,
        'quantity' => 2,
        'unit_of_measure' => 'each',
    ];

    $response = $this->postJson(route('api.billOfMaterials.store', [
        'part' => $nonManufacturedPart,
    ]), $payload);

    $response->assertStatus(422);
});

test('store returns 422 when child part is the same as parent part', function () {
    $payload = [
        'child_part_id' => $this->part->id,
        'quantity' => 1,
        'unit_of_measure' => 'each',
    ];

    $response = $this->postJson(route('api.billOfMaterials.store', [
        'part' => $this->part,
    ]), $payload);

    $response->assertStatus(422);
});

test('store returns 422 when required fields are missing', function () {
    $response = $this->postJson(route('api.billOfMaterials.store', [
        'part' => $this->part,
    ]), []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['child_part_id', 'quantity']);
});

test('store returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update(['role_id' => $role->id]);

    $this->actingAs($user, 'sanctum');

    $childPart = Part::factory()->create();

    $response = $this->postJson(route('api.billOfMaterials.store', [
        'part' => $this->part,
    ]), [
        'child_part_id' => $childPart->id,
        'quantity' => 1,
        'unit_of_measure' => 'each',
    ]);

    $response->assertStatus(403);
});

/**
 * ------------------------------------------------------------
 * -------------------------- Update --------------------------
 * ------------------------------------------------------------
 */
test('update modifies an existing bill of material', function () {
    $childPart = Part::factory()->create();

    $billOfMaterial = BillOfMaterial::factory()->create([
        'parent_part_id' => $this->part->id,
        'child_part_id' => $childPart->id,
        'quantity' => 1,
    ]);

    $payload = [
        'quantity' => 5,
        'notes' => 'Updated notes',
    ];

    $response = $this->putJson(route('api.billOfMaterials.update', [
        'part' => $this->part,
        'billOfMaterial' => $billOfMaterial,
    ]), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['quantity' => '5.0000', 'notes' => 'Updated notes']);
    $this->assertDatabaseHas('bill_of_materials', [
        'id' => $billOfMaterial->id,
        'quantity' => '5.0000',
    ]);
});

test('update returns 403 when user lacks permission', function () {
    $billOfMaterial = BillOfMaterial::factory()->create([
        'parent_part_id' => $this->part->id,
    ]);

    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update(['role_id' => $role->id]);

    $this->actingAs($user, 'sanctum');

    $response = $this->putJson(route('api.billOfMaterials.update', [
        'part' => $this->part,
        'billOfMaterial' => $billOfMaterial,
    ]), ['quantity' => 5]);

    $response->assertStatus(403);
});

test('update returns 404 for non-existent bill of material', function () {
    $response = $this->putJson(route('api.billOfMaterials.update', [
        'part' => $this->part,
        'billOfMaterial' => 999999,
    ]), ['quantity' => 5]);

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Destroy --------------------------
 * -------------------------------------------------------------
 */
test('destroy soft deletes a bill of material and returns 204', function () {
    $billOfMaterial = BillOfMaterial::factory()->create([
        'parent_part_id' => $this->part->id,
    ]);

    $response = $this->deleteJson(route('api.billOfMaterials.destroy', [
        'part' => $this->part,
        'billOfMaterial' => $billOfMaterial,
    ]));

    $response->assertStatus(204);
    $this->assertSoftDeleted('bill_of_materials', ['id' => $billOfMaterial->id]);
});

test('destroy returns 403 when user lacks permission', function () {
    $billOfMaterial = BillOfMaterial::factory()->create([
        'parent_part_id' => $this->part->id,
    ]);

    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update(['role_id' => $role->id]);

    $this->actingAs($user, 'sanctum');

    $response = $this->deleteJson(route('api.billOfMaterials.destroy', [
        'part' => $this->part,
        'billOfMaterial' => $billOfMaterial,
    ]));

    $response->assertStatus(403);
});

test('destroy returns 404 for non-existent bill of material', function () {
    $response = $this->deleteJson(route('api.billOfMaterials.destroy', [
        'part' => $this->part,
        'billOfMaterial' => 999999,
    ]));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Restore --------------------------
 * -------------------------------------------------------------
 */
test('restore recovers a soft deleted bill of material', function () {
    $billOfMaterial = BillOfMaterial::factory()->create([
        'parent_part_id' => $this->part->id,
    ]);
    $billOfMaterial->delete();

    $this->assertSoftDeleted('bill_of_materials', ['id' => $billOfMaterial->id]);

    $response = $this->postJson(route('api.billOfMaterials.restore', [
        'part' => $this->part,
        'billOfMaterial' => $billOfMaterial->id,
    ]));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $billOfMaterial->id]);
    $this->assertDatabaseHas('bill_of_materials', [
        'id' => $billOfMaterial->id,
        'deleted_at' => null,
    ]);
});

test('restore returns 403 when user lacks permission', function () {
    $billOfMaterial = BillOfMaterial::factory()->create([
        'parent_part_id' => $this->part->id,
    ]);
    $billOfMaterial->delete();

    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update(['role_id' => $role->id]);

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.billOfMaterials.restore', [
        'part' => $this->part,
        'billOfMaterial' => $billOfMaterial->id,
    ]));

    $response->assertStatus(403);
});

test('restore returns 404 for non-existent bill of material', function () {
    $response = $this->postJson(route('api.billOfMaterials.restore', [
        'part' => $this->part,
        'billOfMaterial' => 999999,
    ]));

    $response->assertStatus(404);
});

test('restore returns 404 when bill of material is not deleted', function () {
    $billOfMaterial = BillOfMaterial::factory()->create([
        'parent_part_id' => $this->part->id,
    ]);

    $response = $this->postJson(route('api.billOfMaterials.restore', [
        'part' => $this->part,
        'billOfMaterial' => $billOfMaterial->id,
    ]));

    $response->assertStatus(404);
});