<?php

use App\Models\Attachment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Services\Attachments\AttachmentAttacherService;
use App\Services\Attachments\AttachmentFileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth = User::factory()->create();

    $permissions = [
        'attachments.view.all',
        'attachments.create',
        'attachments.update.any',
        'attachments.delete.any',
        'attachments.restore.any',
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
test('index returns paginated attachments and respects per_page query', function () {
    // create uploader user and attachments — ensure attachable_type/id are present to satisfy NOT NULL constraints
    $uploader = User::factory()->create();
    Attachment::factory()->count(12)->create([
        'uploaded_by' => $uploader->id,
        'attachable_type' => User::class,
        'attachable_id' => $uploader->id,
    ]);

    $response = $this->getJson(route('api.attachments.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $this->assertCount(5, $response->json('data'));
});

test('index returns all attachments when no pagination specified', function () {
    Attachment::factory()->count(3)->create();

    $response = $this->getJson(route('api.attachments.index'));

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

    $response = $this->getJson(route('api.attachments.index'));

    $response->assertStatus(403);
});

/**
 * ----------------------------------------------------------
 * -------------------------- Show --------------------------
 * ----------------------------------------------------------
 */
test('show returns the attachment with uploader relationship loaded', function () {
    $uploader = User::factory()->create();

    // create a valid attachment record — include attachable_type/id
    $attachment = Attachment::factory()->create([
        'uploaded_by' => $uploader->id,
        'attachable_type' => User::class,
        'attachable_id' => $uploader->id,
    ]);

    $response = $this->getJson(route('api.attachments.show', $attachment));

    $response->assertStatus(200);

    $response->assertJsonFragment(['id' => $attachment->id]);
    $response->assertJsonStructure([
        'id',
        'filename',
        'disk',
        'path',
        'size',
        'mime',
        'uploaded_by',
        'uploader' => ['id', 'name', 'email'],
    ]);
});

test('show returns 403 when user lacks permission', function () {
    $attachment = Attachment::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.attachments.show', $attachment->id));

    $response->assertStatus(403);
});

test('show returns 404 for non-existent attachment', function () {
    $response = $this->getJson(route('api.attachments.show', 999999));

    $response->assertStatus(404);
});


/**
 * ---------------------------------------------------------
 * ------------------------- Store -------------------------
 * ---------------------------------------------------------
 */
test('store saves uploaded file, creates attachment and calls attacher', function () {
    Storage::fake('public');

    $attacherMock = Mockery::mock(AttachmentAttacherService::class);

    $attacherMock->shouldReceive('attach')
        ->once()
        ->with(Task::class, 1, Mockery::type(Attachment::class));
    
    $this->app->instance(AttachmentAttacherService::class, $attacherMock);

    $fakePath = 'attachments/document.pdf';
    $returnedAttachment = Attachment::factory()->create([
        'filename' => 'document.pdf',
        'disk' => 'public',
        'path' => $fakePath,
        'size' => 120_000,
        'mime' => 'application/pdf',
        'uploaded_by' => $this->auth->id,
        'attachable_type' => Task::class,
        'attachable_id' => $this->auth->id,
    ]);

    Storage::disk('public')->put($fakePath, 'fake-pdf-content');

    // Mock AttachmentService::storeFile to return the prepared attachment
    $serviceMock = Mockery::mock(AttachmentFileService::class);
    $serviceMock->shouldReceive('storeFile')
        ->once()
        ->andReturn($returnedAttachment);
    $this->app->instance(AttachmentFileService::class, $serviceMock);

    $file = UploadedFile::fake()->create('document.pdf', 120, 'application/pdf');

    $payload = [
        'file' => $file,
        'attachable_type' => Task::class,
        'attachable_id' => 1, 
        'uploaded_by' => $this->auth->id,
    ];

    $response = $this->postJson(route('api.attachments.store'), $payload);

    $response->assertStatus(201);

    $response->assertJsonFragment(['filename' => 'document.pdf']);
    $response->assertJsonStructure(['uploader' => ['id', 'name', 'email']]);

    $this->assertDatabaseHas('attachments', [
        'filename' => 'document.pdf',
        'uploaded_by' => $this->auth->id,
        'disk' => 'public',
    ]);

    // Assert that the file exists on the fake public disk at the path returned by service
    Storage::disk('public')->assertExists($returnedAttachment->path);
});

test('store returns 403 when user lacks permission', function () {
    $attachment = Attachment::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.attachments.store', $attachment->id));

    $response->assertStatus(403);
});

/**
 * -----------------------------------------------------------
 * ------------------------- Destroy -------------------------
 * -----------------------------------------------------------
 */
test('destroy deletes file from disk (if present) and deletes the model', function () {
    Storage::fake('public');

    // Put a fake file in storage and make an attachment record pointing to it.
    // Include attachable_type/id so factory insert doesn't violate NOT NULL.
    $path = 'attachments/fake-file.txt';
    Storage::disk('public')->put($path, 'hello world');

    $attachment = Attachment::factory()->create([
        'disk' => 'public',
        'path' => $path,
        'attachable_type' => User::class,
        'attachable_id' => $this->auth->id,
        'uploaded_by' => $this->auth->id,
    ]);

    // ensure file exists before delete
    Storage::disk('public')->assertExists($path);

    $response = $this->deleteJson(route('api.attachments.destroy', $attachment));

    $response->assertStatus(204);

    // file should be gone and model deleted
    Storage::disk('public')->assertMissing($attachment->path);
    $this->assertSoftDeleted('attachments', ['id' => $attachment->id]);
});

test('destroy returns 403 when user lacks permission', function () {
    $attachment = Attachment::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->getJson(route('api.attachments.destroy', $attachment->id));

    $response->assertStatus(403);
});

test('destroy returns 404 for non-existent attachment', function () {
    $response = $this->deleteJson(route('api.attachments.destroy', 999999));

    $response->assertStatus(404);
});

/**
 * -----------------------------------------------------------
 * ------------------------- Restore -------------------------
 * -----------------------------------------------------------
 */
test('restore deleted attachment', function () {
    Storage::fake('public');

    $path = 'attachments/file.txt';
    Storage::disk('public')->put($path, 'hello');

    $attachment = Attachment::factory()->create([
        'disk' => 'public',
        'path' => $path,
        'attachable_type' => User::class,
        'attachable_id' => $this->auth->id,
        'uploaded_by' => $this->auth->id,
    ]);

    $attachment->delete();

    $this->assertSoftDeleted('attachments', [
        'id' => $attachment->id,
    ]);

    $response = $this->postJson(route('api.attachments.restore', $attachment->id));

    $response->assertStatus(200);

    $this->assertNotSoftDeleted('attachments', [
        'id' => $attachment->id,
    ]);
});

test('restore returns 403 when user lacks permission', function () {
    $attachment = Attachment::factory()->create(['created_by' => $this->auth->id]);
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'user']);

    $user->update([
        'role_id' => $role->id
    ]);

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('api.attachments.restore', $attachment->id));

    $response->assertStatus(403);
});

test('restore returns 404 for non-existent attachment', function () {
    $response = $this->postJson(route('api.attachments.restore', 999999));

    $response->assertStatus(404);
});

test('restore returns 404 when attachment is not deleted', function () {
    $attachment = Attachment::factory()->create();

    $response = $this->postJson(route('api.attachments.restore', $attachment->id));

    $response->assertStatus(404);
});
