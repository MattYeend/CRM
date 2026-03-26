<?php

use App\Models\Company;
use App\Models\Deal;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'deals.view.all',
        'deals.create',
        'deals.update.any',
        'deals.delete.any',
        'deals.restore.any',
        'deals.products.add',
        'deals.products.update',
        'deals.products.remove',
        'deals.products.restore',
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
test('index returns paginated deals with relations and filters', function () {
    $owner = User::factory()->create();
    $company = Company::factory()->create();

    Deal::factory()->count(12)->create([
        'owner_id' => $owner->id,
        'company_id' => $company->id,
        'status' => 'open',
    ]);

    $response = $this->actingAs($this->auth, 'sanctum')
                     ->getJson(route('api.deals.index', ['per_page' => 5, 'status' => 'open', 'owner_id' => $owner->id]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);
    $this->assertCount(5, $response->json('data'));
});

/**
 * ----------------------------------------------------------
 * -------------------------- Show --------------------------
 * ----------------------------------------------------------
 */
test('show returns a deal with relations loaded', function () {
    $deal = Deal::factory()->create();

    $response = $this->actingAs($this->auth, 'sanctum')
                     ->getJson(route('api.deals.show', $deal));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $deal->id]);
    $response->assertJsonStructure([
        'id',
        'title',
        'status',
        'value',
        'currency',
        'company' => [],
        'owner' => [],
        'pipeline' => [],
        'stage' => [],
        'notes' => [],
        'tasks' => [],
        'attachments' => [],
    ]);
});

/**
 * ---------------------------------------------------------
 * ------------------------- Store -------------------------
 * ---------------------------------------------------------
 */
test('store creates a new deal and returns 201', function () {
    $company = Company::factory()->create();
    $owner = User::factory()->create();

    $payload = [
        'title' => 'New Deal',
        'company_id' => $company->id,
        'owner_id' => $owner->id,
        'status' => 'open',
        'value' => 1000,
        'currency' => 'USD',
    ];

    $response = $this->actingAs($this->auth, 'sanctum')
                     ->postJson(route('api.deals.store'), $payload);

    $response->assertStatus(201);
    $response->assertJsonFragment(['title' => 'New Deal', 'status' => 'open']);
    $this->assertDatabaseHas('deals', ['title' => 'New Deal', 'owner_id' => $owner->id]);
});

/**
 * ----------------------------------------------------------
 * ------------------------- Update -------------------------
 * ----------------------------------------------------------
 */
test('update modifies an existing deal', function () {
    $deal = Deal::factory()->create(['title' => 'Old Deal']);

    $payload = [
        'title' => 'Updated Deal',
        'status' => 'won',
    ];

    $response = $this->actingAs($this->auth, 'sanctum')
                     ->putJson(route('api.deals.update', $deal), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment(['title' => 'Updated Deal', 'status' => 'won']);
    $this->assertDatabaseHas('deals', ['id' => $deal->id, 'title' => 'Updated Deal', 'status' => 'won']);
});

/**
 * -----------------------------------------------------------
 * ------------------------- Destroy -------------------------
 * -----------------------------------------------------------
 */
test('destroy deletes the deal', function () {
    $deal = Deal::factory()->create();

    $response = $this->actingAs($this->auth, 'sanctum')
                     ->deleteJson(route('api.deals.destroy', $deal));

    $response->assertStatus(204);

    if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses(Deal::class))) {
        $this->assertSoftDeleted('deals', ['id' => $deal->id]);
    } else {
        $this->assertDatabaseMissing('deals', ['id' => $deal->id]);
    }
});

/**
 * -----------------------------------------------------------
 * ------------------------- Restore -------------------------
 * -----------------------------------------------------------
 */
test('restore recovers a soft-deleted deal', function () {
    $deal = Deal::factory()->create();
    $deal->delete();

    $response = $this->postJson(route('api.deals.restore', $deal->id));

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $deal->id]);

    $this->assertDatabaseHas('deals', ['id' => $deal->id, 'deleted_at' => null]);
});

/**
 * ------------------------------------------------------------
 * ------------------------- Products -------------------------
 * ------------------------------------------------------------
 */
test('add products to a deal', function () {

    $deal = Deal::factory()->create();
    $product = Product::factory()->create();

    $payload = [
        'products' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'price' => 25.50,
                'meta' => ['color' => 'red'],
            ]
        ]
    ];

    $response = $this->postJson(route('api.deals.products.add', $deal), $payload);

    $response->assertStatus(200);
    $response->assertJsonFragment([
        'message' => 'Products added to deal'
    ]);

    $this->assertDatabaseHas('deal_products', [
        'deal_id' => $deal->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 25.50,
    ]);
});

test('update products on a deal', function () {

    $deal = Deal::factory()->create();
    $product = Product::factory()->create();

    // Attach first
    $deal->products()->attach($product->id, ['quantity' => 1, 'price' => 10]);

    $payload = [
        'products' => [
            [
                'product_id' => $product->id,
                'quantity' => 5,
                'price' => 50,
            ]
        ]
    ];

    $response = $this->putJson(route('api.deals.products.update', $deal), $payload);

    $response->assertStatus(200);

    $this->assertDatabaseHas('deal_products', [
        'deal_id' => $deal->id,
        'product_id' => $product->id,
        'quantity' => 5,
        'price' => 50,
    ]);
});

test('remove a product from a deal', function () {

    $deal = Deal::factory()->create();
    $product = Product::factory()->create();

    // Attach first
    $deal->products()->attach($product->id, ['quantity' => 1, 'price' => 10]);

    $response = $this->deleteJson(route('api.deals.products.remove', [$deal, $product]));

    $response->assertStatus(200);

    // Assert the row is soft-deleted
    $this->assertDatabaseHas('deal_products', [
        'deal_id' => $deal->id,
        'product_id' => $product->id,
    ]);

    $deletedAt = DB::table('deal_products')
        ->where('deal_id', $deal->id)
        ->where('product_id', $product->id)
        ->value('deleted_at');

    $this->assertNotNull($deletedAt, 'Product row should be soft-deleted.');
});

test('restore a previously removed product on a deal', function () {

    $deal = Deal::factory()->create();
    $product = Product::factory()->create();

    // Attach and then detach (soft delete if you implement it)
    $deal->products()->attach($product->id, ['quantity' => 1, 'price' => 10]);
    $deal->products()->detach($product->id);

    // Call restore route
    $response = $this->postJson(route('api.deals.products.restore', [$deal, $product]));

    $response->assertStatus(200);

    $this->assertDatabaseHas('deal_products', [
        'deal_id' => $deal->id,
        'product_id' => $product->id,
    ]);
});