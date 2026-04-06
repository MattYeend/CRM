<?php

use App\Models\Lead;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'leads.view.all',
        'leads.create',
        'leads.update.any',
        'leads.delete.any',
        'leads.restore.any',
        'leads.access.any',
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
 * -----------------------------------------------------------
 * -------------------------- Index --------------------------
 * -----------------------------------------------------------
 */
test('index returns paginated leads with filters', function () {
    $owner = User::factory()->create();

    Lead::factory()->count(12)->create([
        'owner_id' => $owner->id,
        'source' => 'website',
    ]);

    $response = $this->getJson(route('api.leads.index', [
        'per_page' => 5,
        'owner' => $owner->id,
        'source' => 'website',
    ]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('index returns all leads when no pagination specified', function () {
    Lead::factory()->count(3)->create();

    $response = $this->getJson(route('api.leads.index'));

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

    $response = $this->getJson(route('api.leads.index'));

    $response->assertStatus(403);
});

/**
 * ------------------------------------------------------------
 * --------------------------- Show ---------------------------
 * ------------------------------------------------------------
 */
test('show returns a lead with relations loaded', function () {
    $owner = User::factory()->create();
    $assignedTo = User::factory()->create();

    $lead = Lead::factory()->create([
        'owner_id' => $owner->id,
        'assigned_to' => $assignedTo->id,
        'meta' => ['campaign' => 'spring'],
    ]);

    $response = $this->getJson(route('api.leads.show', $lead));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $lead->id]);

    $response->assertJsonStructure([
        'id',
        'title',
        'first_name',
        'last_name',
        'email',
        'phone',
        'source',
        'meta',
        'owner' => [],
        'assigned_to' => [],
        'creator',
        'updater',
        'deleter',
        'created_at',
        'updated_at',
    ]);
});

test('show returns 403 when user lacks permission', function () {
    $lead = Lead::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.leads.show', $lead->id));

    $response->assertStatus(403);
});

test('show returns 404 for non-existent lead', function () {
    $response = $this->getJson(route('api.leads.show', 999999));

    $response->assertStatus(404);
});

/**
 * -----------------------------------------------------------
 * -------------------------- Store --------------------------
 * -----------------------------------------------------------
 */
test('store creates a new lead and returns 201', function () {
    $owner = User::factory()->create();
    $assignedTo = User::factory()->create();

    $payload = [
        'title' => 'New Lead',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'lead@example.com',
        'phone' => '0123456789',
        'source' => 'website',
        'owner_id' => $owner->id,
        'assigned_to' => $assignedTo->id,
        'meta' => ['campaign' => 'summer-sale'],
    ];

    $response = $this->postJson(route('api.leads.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['title' => 'New Lead']);

    $this->assertDatabaseHas('leads', [
        'title' => 'New Lead',
        'owner_id' => $owner->id,
        'assigned_to' => $assignedTo->id,
    ]);
});

test('store returns 403 when user lacks permission', function () {
    $lead = Lead::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.leads.store', $lead->id));

    $response->assertStatus(403);
});

/**
 * ------------------------------------------------------------
 * -------------------------- Update --------------------------
 * ------------------------------------------------------------
 */
test('update modifies an existing lead', function () {
    $lead = Lead::factory()->create(['title' => 'Old Lead']);

    $payload = [
        'title' => 'Updated Lead',
        'source' => 'referral',
    ];

    $response = $this->putJson(route('api.leads.update', $lead), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'title' => 'Updated Lead',
        'source' => 'referral',
    ]);

    $this->assertDatabaseHas('leads', [
        'id' => $lead->id,
        'title' => 'Updated Lead',
        'first_name' => $lead->first_name,
        'last_name' => $lead->last_name,
        'source' => 'referral',
    ]);
});

test('update returns 403 when user lacks permission', function () {
    $lead = Lead::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.leads.update', $lead->id));

    $response->assertStatus(403);
});

test('update returns 404 for non-existent lead', function () {
    $response = $this->putJson(route('api.leads.update', 999999), ['name' => 'Ghost']);

    $response->assertStatus(404);
});

/**
 * -----------------------------------------------------------
 * ------------------------- Destroy -------------------------
 * -----------------------------------------------------------
 */
test('destroy soft deletes the lead', function () {
    $lead = Lead::factory()->create();

    $response = $this->deleteJson(route('api.leads.destroy', $lead));

    $response->assertStatus(204);

    if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses(Lead::class))) {
        $this->assertSoftDeleted('leads', ['id' => $lead->id]);
    } else {
        $this->assertDatabaseMissing('leads', ['id' => $lead->id]);
    }
});

test('destroy returns 403 when user lacks permission', function () {
    $lead = Lead::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.leads.destroy', $lead->id));

    $response->assertStatus(403);
});

test('destroy returns 404 for non-existent lead', function () {
    $response = $this->deleteJson(route('api.leads.destroy', 999999));

    $response->assertStatus(404);
});

/**
 * -----------------------------------------------------------
 * ------------------------- Restore -------------------------
 * -----------------------------------------------------------
 */
test('restore recovers a soft deleted lead', function () {
    $lead = Lead::factory()->create();
    $lead->delete();

    $response = $this->postJson(route('api.leads.restore', $lead->id));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $lead->id]);

    $this->assertDatabaseHas('leads', [
        'id' => $lead->id,
        'deleted_at' => null,
    ]);
});

test('restore returns 403 when user lacks permission', function () {
    $lead = Lead::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.leads.restore', $lead->id));

    $response->assertStatus(403);
});

test('restore returns 404 for non-existent lead', function () {
    $response = $this->postJson(route('api.leads.restore', 999999));

    $response->assertStatus(404);
});

test('restore returns 404 when lead is not deleted', function () {
    $lead = Lead::factory()->create();

    $response = $this->postJson(route('api.leads.restore', $lead->id));

    $response->assertStatus(404);
});
