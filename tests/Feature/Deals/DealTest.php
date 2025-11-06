<?php

use App\Models\Deal;
use App\Models\User;
use App\Models\Company;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create an authenticated user for API requests
    $this->auth = User::factory()->create();
    $this->actingAs($this->auth, 'sanctum');

    // Disable throttle middleware for tests
    $this->withoutMiddleware(ThrottleRequests::class);
});

test('index returns paginated deals with relations and filters', function () {
    $owner = User::factory()->create();
    $company = Company::factory()->create();
    $contact = Contact::factory()->create(['company_id' => $company->id]);

    Deal::factory()->count(12)->create([
        'owner_id' => $owner->id,
        'company_id' => $company->id,
        'contact_id' => $contact->id,
        'status' => 'open',
    ]);

    $response = $this->actingAs($this->auth, 'sanctum')
                     ->getJson(route('deals.index', ['per_page' => 5, 'status' => 'open', 'owner_id' => $owner->id]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

test('show returns a deal with relations loaded', function () {
    $deal = Deal::factory()->create();

    $response = $this->actingAs($this->auth, 'sanctum')
                     ->getJson(route('deals.show', $deal));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $deal->id]);
    $response->assertJsonStructure([
        'id',
        'title',
        'status',
        'value',
        'currency',
        'company' => [],
        'contact' => [],
        'owner' => [],
        'pipeline' => [],
        'stage' => [],
        'notes' => [],
        'tasks' => [],
        'attachments' => [],
    ]);
});

test('store creates a new deal and returns 201', function () {
    $company = Company::factory()->create();
    $contact = Contact::factory()->create(['company_id' => $company->id]);
    $owner = User::factory()->create();

    $payload = [
        'title' => 'New Deal',
        'company_id' => $company->id,
        'contact_id' => $contact->id,
        'owner_id' => $owner->id,
        'status' => 'open',
        'value' => 1000,
        'currency' => 'USD',
    ];

    $response = $this->actingAs($this->auth, 'sanctum')
                     ->postJson(route('deals.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['title' => 'New Deal', 'status' => 'open']);
    $this->assertDatabaseHas('deals', ['title' => 'New Deal', 'owner_id' => $owner->id]);
});

test('update modifies an existing deal', function () {
    $deal = Deal::factory()->create(['title' => 'Old Deal']);

    $payload = [
        'title' => 'Updated Deal',
        'status' => 'won',
    ];

    $response = $this->actingAs($this->auth, 'sanctum')
                     ->putJson(route('deals.update', $deal), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['title' => 'Updated Deal', 'status' => 'won']);
    $this->assertDatabaseHas('deals', ['id' => $deal->id, 'title' => 'Updated Deal', 'status' => 'won']);
});

test('destroy deletes the deal', function () {
    $deal = Deal::factory()->create();

    $response = $this->actingAs($this->auth, 'sanctum')
                     ->deleteJson(route('deals.destroy', $deal));

    $response->assertStatus(204);

    if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses(Deal::class))) {
        $this->assertSoftDeleted('deals', ['id' => $deal->id]);
    } else {
        $this->assertDatabaseMissing('deals', ['id' => $deal->id]);
    }
});

test('restore recovers a soft-deleted deal', function () {
    if (!in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses(Deal::class))) {
        $this->markTestSkipped('Deal model does not use SoftDeletes.');
    }

    $deal = Deal::factory()->create();
    $deal->delete();

    $response = $this->actingAs($this->auth, 'sanctum')
                     ->postJson(route('deals.restore', $deal->id));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $deal->id]);

    $this->assertDatabaseHas('deals', ['id' => $deal->id, 'deleted_at' => null]);
});