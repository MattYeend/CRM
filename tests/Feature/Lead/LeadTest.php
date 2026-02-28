<?php

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();
    $this->actingAs($this->auth, 'sanctum');

    $this->withoutMiddleware(ThrottleRequests::class);
});

test('index returns paginated leads with filters', function () {
    $owner = User::factory()->create();

    Lead::factory()->count(12)->create([
        'owner_id' => $owner->id,
        'source' => 'website',
    ]);

    $response = $this->getJson(route('leads.index', [
        'per_page' => 5,
        'owner_id' => $owner->id,
        'source' => 'website',
    ]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('show returns a lead with relations loaded', function () {
    $owner = User::factory()->create();
    $assignedTo = User::factory()->create();

    $lead = Lead::factory()->create([
        'owner_id' => $owner->id,
        'assigned_to' => $assignedTo->id,
        'meta' => ['campaign' => 'spring'],
    ]);

    $response = $this->getJson(route('leads.show', $lead));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $lead->id]);

    $response->assertJsonStructure([
        'id',
        'name',
        'email',
        'phone',
        'source',
        'meta',
        'owner_id' => [],
        'assigned_to' => [],
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
    ]);
});

test('store creates a new lead and returns 201', function () {
    $owner = User::factory()->create();
    $assignedTo = User::factory()->create();

    $payload = [
        'name' => 'New Lead',
        'email' => 'lead@example.com',
        'phone' => '0123456789',
        'source' => 'website',
        'owner_id' => $owner->id,
        'assigned_to' => $assignedTo->id,
        'meta' => ['campaign' => 'summer-sale'],
    ];

    $response = $this->postJson(route('leads.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['name' => 'New Lead']);

    $this->assertDatabaseHas('leads', [
        'name' => 'New Lead',
        'owner_id' => $owner->id,
        'assigned_to' => $assignedTo->id,
    ]);
});

test('update modifies an existing lead', function () {
    $lead = Lead::factory()->create(['name' => 'Old Lead']);

    $payload = [
        'name' => 'Updated Lead',
        'source' => 'referral',
    ];

    $response = $this->putJson(route('leads.update', $lead), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'name' => 'Updated Lead',
        'source' => 'referral',
    ]);

    $this->assertDatabaseHas('leads', [
        'id' => $lead->id,
        'name' => 'Updated Lead',
        'source' => 'referral',
    ]);
});

test('destroy soft deletes the lead', function () {
    $lead = Lead::factory()->create();

    $response = $this->deleteJson(route('leads.destroy', $lead));

    $response->assertStatus(204);

    if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses(Lead::class))) {
        $this->assertSoftDeleted('leads', ['id' => $lead->id]);
    } else {
        $this->assertDatabaseMissing('leads', ['id' => $lead->id]);
    }
});

test('restore recovers a soft deleted lead', function () {
    if (!in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses(Lead::class))) {
        $this->markTestSkipped('Lead model does not use SoftDeletes.');
    }

    $lead = Lead::factory()->create();
    $lead->delete();

    $response = $this->postJson(route('leads.restore', $lead->id));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $lead->id]);

    $this->assertDatabaseHas('leads', [
        'id' => $lead->id,
        'deleted_at' => null,
    ]);
});