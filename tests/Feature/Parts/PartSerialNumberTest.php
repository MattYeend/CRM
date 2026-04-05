<?php

use App\Models\Part;
use App\Models\PartSerialNumber;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'partSerialNumbers.view.all',
        'partSerialNumbers.create',
        'partSerialNumbers.update.any',
        'partSerialNumbers.delete.any',
        'partSerialNumbers.restore.any',
        'partSerialNumbers.access.any',
    ];

    $permissionModels = collect($permissions)
        ->map(fn($name) => Permission::firstOrCreate(['name' => $name]));

    $role = Role::factory()->create(['name' => 'admin']);
    $role->permissions()->sync($permissionModels->pluck('id'));

    $this->auth->update(['role_id' => $role->id]);

    $this->part = Part::factory()->create();

    $this->actingAs($this->auth, 'sanctum');

    $this->withoutMiddleware(ThrottleRequests::class);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Index ---------------------------
 * -------------------------------------------------------------
 */
test('index returns paginated part serial numbers', function () {
    PartSerialNumber::factory()->count(12)->create(['part_id' => $this->part->id]);

    $response = $this->getJson(
        route('api.partSerialNumbers.index', ['part' => $this->part->id, 'per_page' => 5])
    );

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('index returns all part serial numbers when no pagination specified', function () {
    PartSerialNumber::factory()->count(3)->create(['part_id' => $this->part->id]);

    $response = $this->getJson(
        route('api.partSerialNumbers.index', ['part' => $this->part->id])
    );

    $response->assertStatus(200);
    $this->assertCount(3, $response->json('data'));
});

test('index only returns serial numbers belonging to the given part', function () {
    $otherPart = Part::factory()->create();

    PartSerialNumber::factory()->count(2)->create(['part_id' => $this->part->id]);
    PartSerialNumber::factory()->count(3)->create(['part_id' => $otherPart->id]);

    $response = $this->getJson(
        route('api.partSerialNumbers.index', ['part' => $this->part->id])
    );

    $response->assertStatus(200);
    $this->assertCount(2, $response->json('data'));
});

test('index returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);
    $user->update(['role_id' => $role->id]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(
        route('api.partSerialNumbers.index', ['part' => $this->part->id])
    );

    $response->assertStatus(403);
});

/**
 * -------------------------------------------------------------
 * --------------------------- Store ---------------------------
 * -------------------------------------------------------------
 */
test('store creates a new part serial number and returns 201', function () {
    $payload = [
        'serial_number' => 'SN-001',
        'status' => 'in_stock',
    ];

    $response = $this->postJson(
        route('api.partSerialNumbers.store', ['part' => $this->part->id]),
        $payload
    );

    $response->assertStatus(201);
    $response->assertJsonFragment(['serial_number' => 'SN-001']);
    $this->assertDatabaseHas('part_serial_numbers', [
        'part_id' => $this->part->id,
        'serial_number' => 'SN-001',
    ]);
});

test('store returns 403 when user lacks permission', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);
    $user->update(['role_id' => $role->id]);

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(
        route('api.partSerialNumbers.store', ['part' => $this->part->id]),
        ['serial_number' => 'SN-002']
    );

    $response->assertStatus(403);
});

test('store returns 422 when required fields are missing', function () {
    $response = $this->postJson(
        route('api.partSerialNumbers.store', ['part' => $this->part->id]),
        []
    );

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['serial_number']);
});

test('store returns 422 when serial is not unique for the part', function () {
    PartSerialNumber::factory()->create([
        'part_id' => $this->part->id,
        'serial_number' => 'SN-DUPE',
    ]);

    $response = $this->postJson(
        route('api.partSerialNumbers.store', ['part' => $this->part->id]),
        ['serial_number' => 'SN-DUPE']
    );

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['serial_number']);
});

test('store returns 404 for non-existent part', function () {
    $response = $this->postJson(
        route('api.partSerialNumbers.store', ['part' => 999999]),
        ['serial_number' => 'SN-003']
    );

    $response->assertStatus(404);
});

/**
 * ------------------------------------------------------------
 * -------------------------- Update --------------------------
 * ------------------------------------------------------------
 */
test('update modifies an existing part serial number', function () {
    $serialNumber = PartSerialNumber::factory()->create([
        'part_id' => $this->part->id,
        'serial_number' => 'SN-OLD',
    ]);

    $response = $this->putJson(
        route('api.partSerialNumbers.update', [
            'part' => $this->part->id,
            'serialNumber' => $serialNumber->id,
        ]),
        ['serial_number' => 'SN-NEW']
    );

    $response->assertStatus(200);
    $response->assertJsonFragment(['serial_number' => 'SN-NEW']);
    $this->assertDatabaseHas('part_serial_numbers', [
        'id' => $serialNumber->id,
        'serial_number' => 'SN-NEW',
    ]);
});

test('update allows saving without changing serial number', function () {
    $serialNumber = PartSerialNumber::factory()->create([
        'part_id' => $this->part->id,
        'serial_number' => 'SN-STABLE',
    ]);

    $response = $this->putJson(
        route('api.partSerialNumbers.update', [
            'part' => $this->part->id,
            'serialNumber' => $serialNumber->id,
        ]),
        ['status' => 'sold']
    );

    $response->assertStatus(200);
    $this->assertDatabaseHas('part_serial_numbers', [
        'id' => $serialNumber->id,
        'serial_number' => 'SN-STABLE',
        'status' => 'sold',
    ]);
});

test('update returns 403 when user lacks permission', function () {
    $serialNumber = PartSerialNumber::factory()->create(['part_id' => $this->part->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);
    $user->update(['role_id' => $role->id]);

    $this->actingAs($user, 'sanctum');

    $response = $this->putJson(
        route('api.partSerialNumbers.update', [
            'part' => $this->part->id,
            'serialNumber' => $serialNumber->id,
        ]),
        ['serial_number' => 'SN-HACK']
    );

    $response->assertStatus(403);
});

test('update returns 404 for non-existent part serial number', function () {
    $response = $this->putJson(
        route('api.partSerialNumbers.update', [
            'part' => $this->part->id,
            'serialNumber' => 999999,
        ]),
        ['serial_number' => 'SN-GHOST']
    );

    $response->assertStatus(404);
});

test('update returns 422 when serial conflicts with another serial number on the same part', function () {
    PartSerialNumber::factory()->create([
        'part_id' => $this->part->id,
        'serial_number' => 'SN-TAKEN',
    ]);

    $serialNumber = PartSerialNumber::factory()->create([
        'part_id' => $this->part->id,
        'serial_number' => 'SN-MINE',
    ]);

    $response = $this->putJson(
        route('api.partSerialNumbers.update', [
            'part' => $this->part->id,
            'serialNumber' => $serialNumber->id,
        ]),
        ['serial_number' => 'SN-TAKEN']
    );

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['serial_number']);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Destroy --------------------------
 * -------------------------------------------------------------
 */
test('destroy soft deletes a part serial number and returns 204', function () {
    $serialNumber = PartSerialNumber::factory()->create(['part_id' => $this->part->id]);

    $response = $this->deleteJson(
        route('api.partSerialNumbers.destroy', [
            'part' => $this->part->id,
            'serialNumber' => $serialNumber->id,
        ])
    );

    $response->assertStatus(204);
    $this->assertSoftDeleted('part_serial_numbers', ['id' => $serialNumber->id]);
});

test('destroy returns 403 when user lacks permission', function () {
    $serialNumber = PartSerialNumber::factory()->create(['part_id' => $this->part->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);
    $user->update(['role_id' => $role->id]);

    $this->actingAs($user, 'sanctum');

    $response = $this->deleteJson(
        route('api.partSerialNumbers.destroy', [
            'part' => $this->part->id,
            'serialNumber' => $serialNumber->id,
        ])
    );

    $response->assertStatus(403);
});

test('destroy returns 404 for non-existent part serial number', function () {
    $response = $this->deleteJson(
        route('api.partSerialNumbers.destroy', [
            'part' => $this->part->id,
            'serialNumber' => 999999,
        ])
    );

    $response->assertStatus(404);
});

/**
 * -------------------------------------------------------------
 * -------------------------- Restore --------------------------
 * -------------------------------------------------------------
 */
test('restore recovers a soft deleted part serial number', function () {
    $serialNumber = PartSerialNumber::factory()->create(['part_id' => $this->part->id]);
    $serialNumber->delete();

    $this->assertSoftDeleted('part_serial_numbers', ['id' => $serialNumber->id]);

    $response = $this->postJson(
        route('api.partSerialNumbers.restore', [
            'part' => $this->part->id,
            'id' => $serialNumber->id,
        ])
    );

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $serialNumber->id]);
    $this->assertDatabaseHas('part_serial_numbers', [
        'id' => $serialNumber->id,
        'deleted_at' => null,
    ]);
});

test('restore returns 403 when user lacks permission', function () {
    $serialNumber = PartSerialNumber::factory()->create(['part_id' => $this->part->id]);
    $serialNumber->delete();

    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);
    $user->update(['role_id' => $role->id]);

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(
        route('api.partSerialNumbers.restore', [
            'part' => $this->part->id,
            'id' => $serialNumber->id,
        ])
    );

    $response->assertStatus(403);
});

test('restore returns 404 for non-existent part serial number', function () {
    $response = $this->postJson(
        route('api.partSerialNumbers.restore', [
            'part' => $this->part->id,
            'id' => 999999,
        ])
    );

    $response->assertStatus(404);
});

test('restore returns 404 when part serial number is not deleted', function () {
    $serialNumber = PartSerialNumber::factory()->create(['part_id' => $this->part->id]);

    $response = $this->postJson(
        route('api.partSerialNumbers.restore', [
            'part' => $this->part->id,
            'id' => $serialNumber->id,
        ])
    );

    $response->assertStatus(404);
});
