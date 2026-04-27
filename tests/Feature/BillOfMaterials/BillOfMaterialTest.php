<?php

use App\Models\Part;
use App\Models\Product;
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
    $this->product = Product::factory()->create();
});

/**
 * -------------------------------------------------------------
 * --------------------------- Index ---------------------------
 * -------------------------------------------------------------
 */
test('index returns paginated bill of materials for a part', function () {
    BillOfMaterial::factory()->count(12)->forPart($this->part)->create();

    $response = $this->getJson(route('api.billOfMaterials.parts.index', [
        'part' => $this->part,
        'per_page' => 5,
    ]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('index returns paginated bill of materials for a product', function () {
    BillOfMaterial::factory()->count(8)->forProduct($this->product)->create();

    $response = $this->getJson(route('api.billOfMaterials.products.index', [
        'product' => $this->product,
        'per_page' => 5,
    ]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('index only returns bill of materials scoped to the given part', function () {
    $otherPart = Part::factory()->create(['is_manufactured' => true]);

    BillOfMaterial::factory()->count(2)->forPart($this->part)->create();
    BillOfMaterial::factory()->count(3)->forPart($otherPart)->create();

    $response = $this->getJson(route('api.billOfMaterials.parts.index', [
        'part' => $this->part,
    ]));

    $response->assertStatus(200);
    $this->assertCount(2, $response->json('data'));
});

test('index only returns bill of materials scoped to the given product', function () {
    $otherProduct = Product::factory()->create();

    BillOfMaterial::factory()->count(2)->forProduct($this->product)->create();
    BillOfMaterial::factory()->count(3)->forProduct($otherProduct)->create();

    $response = $this->getJson(route('api.billOfMaterials.products.index', [
        'product' => $this->product,
    ]));

    $response->assertStatus(200);
    $this->assertCount(2, $response->json('data'));
});

test('index returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update(['role_id' => $role->id]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.billOfMaterials.parts.index', [
        'part' => $this->part,
    ]));

    $response->assertStatus(403);
});

test('index returns 404 for non-existent part', function () {
    $response = $this->getJson(route('api.billOfMaterials.parts.index', [
        'part' => 999999,
    ]));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Store ---------------------------
 * -------------------------------------------------------------
 */
test('store creates a new bill of material for a part and returns 201', function () {
    $childPart = Part::factory()->create();

    $payload = [
        'child_part_id' => $childPart->id,
        'quantity' => 3,
        'unit_of_measure' => 'each',
        'notes' => 'Test BOM entry',
    ];

    $response = $this->postJson(route('api.billOfMaterials.parts.store', [
        'part' => $this->part,
    ]), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment([
        'child_part_id' => $childPart->id,
        'manufacturable_id' => $this->part->id,
    ]);
    $this->assertDatabaseHas('bill_of_materials', [
        'manufacturable_type' => Part::class,
        'manufacturable_id' => $this->part->id,
        'child_part_id' => $childPart->id,
    ]);
});

test('store creates a new bill of material for a product and returns 201', function () {
    $childPart = Part::factory()->create();

    $payload = [
        'child_part_id' => $childPart->id,
        'quantity' => 2,
        'unit_of_measure' => 'each',
        'notes' => 'Product BOM entry',
    ];

    $response = $this->postJson(route('api.billOfMaterials.products.store', [
        'product' => $this->product,
    ]), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment([
        'child_part_id' => $childPart->id,
        'manufacturable_id' => $this->product->id,
    ]);
    $this->assertDatabaseHas('bill_of_materials', [
        'manufacturable_type' => Product::class,
        'manufacturable_id' => $this->product->id,
        'child_part_id' => $childPart->id,
    ]);
});

test('store returns 422 when required fields are missing', function () {
    $response = $this->postJson(route('api.billOfMaterials.parts.store', [
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

    $response = $this->postJson(route('api.billOfMaterials.parts.store', [
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
test('update modifies an existing part bill of material', function () {
    $childPart = Part::factory()->create();

    $billOfMaterial = BillOfMaterial::factory()
        ->forPart($this->part)
        ->withChildPart($childPart)
        ->create(['quantity' => 1]);

    $payload = [
        'quantity' => 5,
        'notes' => 'Updated notes',
    ];

    $response = $this->putJson(route('api.billOfMaterials.parts.update', [
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

test('update modifies an existing product bill of material', function () {
    $childPart = Part::factory()->create();

    $billOfMaterial = BillOfMaterial::factory()
        ->forProduct($this->product)
        ->withChildPart($childPart)
        ->create(['quantity' => 2]);

    $payload = [
        'quantity' => 10,
        'scrap_percentage' => 5,
    ];

    $response = $this->putJson(route('api.billOfMaterials.products.update', [
        'product' => $this->product,
        'billOfMaterial' => $billOfMaterial,
    ]), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['quantity' => '10.0000']);
    $this->assertDatabaseHas('bill_of_materials', [
        'id' => $billOfMaterial->id,
        'quantity' => '10.0000',
        'scrap_percentage' => '5.00',
    ]);
});

test('update returns 403 when user lacks permission', function () {
    $billOfMaterial = BillOfMaterial::factory()->forPart($this->part)->create();

    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update(['role_id' => $role->id]);

    $this->actingAs($user, 'sanctum');

    $response = $this->putJson(route('api.billOfMaterials.parts.update', [
        'part' => $this->part,
        'billOfMaterial' => $billOfMaterial,
    ]), ['quantity' => 5]);

    $response->assertStatus(403);
});

test('update returns 404 for non-existent bill of material', function () {
    $response = $this->putJson(route('api.billOfMaterials.parts.update', [
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
test('destroy soft deletes a part bill of material and returns 204', function () {
    $billOfMaterial = BillOfMaterial::factory()->forPart($this->part)->create();

    $response = $this->deleteJson(route('api.billOfMaterials.parts.destroy', [
        'part' => $this->part,
        'billOfMaterial' => $billOfMaterial,
    ]));

    $response->assertStatus(204);
    $this->assertSoftDeleted('bill_of_materials', ['id' => $billOfMaterial->id]);
});

test('destroy soft deletes a product bill of material and returns 204', function () {
    $billOfMaterial = BillOfMaterial::factory()->forProduct($this->product)->create();

    $response = $this->deleteJson(route('api.billOfMaterials.products.destroy', [
        'product' => $this->product,
        'billOfMaterial' => $billOfMaterial,
    ]));

    $response->assertStatus(204);
    $this->assertSoftDeleted('bill_of_materials', ['id' => $billOfMaterial->id]);
});

test('destroy returns 403 when user lacks permission', function () {
    $billOfMaterial = BillOfMaterial::factory()->forPart($this->part)->create();

    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update(['role_id' => $role->id]);

    $this->actingAs($user, 'sanctum');

    $response = $this->deleteJson(route('api.billOfMaterials.parts.destroy', [
        'part' => $this->part,
        'billOfMaterial' => $billOfMaterial,
    ]));

    $response->assertStatus(403);
});

test('destroy returns 404 for non-existent bill of material', function () {
    $response = $this->deleteJson(route('api.billOfMaterials.parts.destroy', [
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
test('restore recovers a soft deleted part bill of material', function () {
    $billOfMaterial = BillOfMaterial::factory()->forPart($this->part)->create();
    $billOfMaterial->delete();

    $this->assertSoftDeleted('bill_of_materials', ['id' => $billOfMaterial->id]);

    $response = $this->postJson(route('api.billOfMaterials.parts.restore', [
        'part' => $this->part,
        'id' => $billOfMaterial->id,
    ]));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $billOfMaterial->id]);
    $this->assertDatabaseHas('bill_of_materials', [
        'id' => $billOfMaterial->id,
        'deleted_at' => null,
    ]);
});

test('restore recovers a soft deleted product bill of material', function () {
    $billOfMaterial = BillOfMaterial::factory()->forProduct($this->product)->create();
    $billOfMaterial->delete();

    $this->assertSoftDeleted('bill_of_materials', ['id' => $billOfMaterial->id]);

    $response = $this->postJson(route('api.billOfMaterials.products.restore', [
        'product' => $this->product,
        'id' => $billOfMaterial->id,
    ]));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $billOfMaterial->id]);
    $this->assertDatabaseHas('bill_of_materials', [
        'id' => $billOfMaterial->id,
        'deleted_at' => null,
    ]);
});

test('restore returns 403 when user lacks permission', function () {
    $billOfMaterial = BillOfMaterial::factory()->forPart($this->part)->create();
    $billOfMaterial->delete();

    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update(['role_id' => $role->id]);

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.billOfMaterials.parts.restore', [
        'part' => $this->part,
        'id' => $billOfMaterial->id,
    ]));

    $response->assertStatus(403);
});

test('restore returns 404 for non-existent bill of material', function () {
    $response = $this->postJson(route('api.billOfMaterials.parts.restore', [
        'part' => $this->part,
        'id' => 999999,
    ]));

    $response->assertStatus(404);
});

test('restore returns 404 when bill of material is not deleted', function () {
    $billOfMaterial = BillOfMaterial::factory()->forPart($this->part)->create();

    $response = $this->postJson(route('api.billOfMaterials.parts.restore', [
        'part' => $this->part,
        'id' => $billOfMaterial->id,
    ]));

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * -------------------- Polymorphic Tests ----------------------
 * -------------------------------------------------------------
 */
test('parts and products can have separate boms with same child part', function () {
    $childPart = Part::factory()->create();

    $partBom = BillOfMaterial::factory()
        ->forPart($this->part)
        ->withChildPart($childPart)
        ->create(['quantity' => 2]);

    $productBom = BillOfMaterial::factory()
        ->forProduct($this->product)
        ->withChildPart($childPart)
        ->create(['quantity' => 5]);

    $this->assertDatabaseCount('bill_of_materials', 2);
    
    expect($partBom->manufacturable)->toBeInstanceOf(Part::class);
    expect($partBom->manufacturable->id)->toBe($this->part->id);
    expect($partBom->quantity)->toBe('2.0000');

    expect($productBom->manufacturable)->toBeInstanceOf(Product::class);
    expect($productBom->manufacturable->id)->toBe($this->product->id);
    expect($productBom->quantity)->toBe('5.0000');
});

test('bom cost calculation works for both parts and products', function () {
    $childPart1 = Part::factory()->create(['cost_price' => 10.00]);
    $childPart2 = Part::factory()->create(['cost_price' => 5.00]);

    // Part BOM
    BillOfMaterial::factory()
        ->forPart($this->part)
        ->withChildPart($childPart1)
        ->create(['quantity' => 2, 'scrap_percentage' => 0]);

    BillOfMaterial::factory()
        ->forPart($this->part)
        ->withChildPart($childPart2)
        ->create(['quantity' => 4, 'scrap_percentage' => 0]);

    // Product BOM
    BillOfMaterial::factory()
        ->forProduct($this->product)
        ->withChildPart($childPart1)
        ->create(['quantity' => 3, 'scrap_percentage' => 0]);

    $this->part->refresh();
    $this->product->refresh();

    expect($this->part->bomCost())->toBe(40.0); // (2 * 10) + (4 * 5)
    expect($this->product->bomCost())->toBe(30.0); // (3 * 10)
});
