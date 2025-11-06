<?php

use App\Models\Attachment;
use App\Models\User;
use App\Services\AttachmentAttacher;
use App\Services\AttachmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * Disable the throttle middleware (your routes use throttle:api).
 */
beforeEach(function () {
    $this->withoutMiddleware(ThrottleRequests::class);
});

test('index returns paginated attachments and respects per_page query', function () {
    $auth = User::factory()->create();
    $this->actingAs($auth, 'sanctum');

    // create uploader user and attachments — ensure attachable_type/id are present to satisfy NOT NULL constraints
    $uploader = User::factory()->create();
    Attachment::factory()->count(12)->create([
        'uploaded_by' => $uploader->id,
        // satisfy DB NOT NULL constraint on attachable columns
        'attachable_type' => User::class,
        'attachable_id' => $uploader->id,
    ]);

    $response = $this->getJson(route('attachments.index', ['per_page' => 5]));

    $response->assertStatus(200);
    $response->assertJsonPath('per_page', 5);

    $this->assertCount(5, $response->json('data'));
});

test('show returns the attachment with uploader relationship loaded', function () {
    $auth = User::factory()->create();
    $this->actingAs($auth, 'sanctum');

    $uploader = User::factory()->create();

    // create a valid attachment record — include attachable_type/id
    $attachment = Attachment::factory()->create([
        'uploaded_by' => $uploader->id,
        'attachable_type' => User::class,
        'attachable_id' => $uploader->id,
    ]);

    $response = $this->getJson(route('attachments.show', $attachment));

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

test('store saves uploaded file, creates attachment and calls attacher', function () {
    // Fake the storage used by AttachmentService
    Storage::fake('public');

    $auth = User::factory()->create();
    $this->actingAs($auth, 'sanctum');

    // Mock the AttachmentAttacher so we can assert attach() is called
    $attacherMock = Mockery::mock(AttachmentAttacher::class);
    $attacherMock->shouldReceive('attach')
        ->once()
        ->withArgs(function ($type, $id, $attachment) use ($auth) {
            return $type === User::class
                && $id === $auth->id
                && $attachment instanceof Attachment;
        });
    $this->app->instance(AttachmentAttacher::class, $attacherMock);

    // Prepare an Attachment model the mocked service will return.
    // Make sure attachable_type/id are present to satisfy NOT NULL constraints.
    $fakePath = 'attachments/document.pdf';
    $returnedAttachment = Attachment::factory()->create([
        'filename' => 'document.pdf',
        'disk' => 'public',
        'path' => $fakePath,
        'size' => 120_000,
        'mime' => 'application/pdf',
        'uploaded_by' => $auth->id,
        'attachable_type' => User::class,
        'attachable_id' => $auth->id,
    ]);

    // Ensure the file exists on the fake disk so later assertExists will pass
    Storage::disk('public')->put($fakePath, 'fake-pdf-content');

    // Mock AttachmentService::storeFile to return the prepared attachment
    $serviceMock = Mockery::mock(AttachmentService::class);
    $serviceMock->shouldReceive('storeFile')
        ->once()
        ->andReturn($returnedAttachment);
    $this->app->instance(AttachmentService::class, $serviceMock);

    // Create a fake file to upload (the mock ignores the contents but controller still expects file)
    $file = UploadedFile::fake()->create('document.pdf', 120, 'application/pdf');

    $payload = [
        'file' => $file,
        'attachable_type' => User::class,
        'attachable_id' => $auth->id,
        'uploaded_by' => $auth->id,
    ];

    $response = $this->postJson(route('attachments.store'), $payload);

    $response->assertStatus(201);

    // Response should include filename and uploader
    $response->assertJsonFragment(['filename' => 'document.pdf']);
    $response->assertJsonStructure(['uploader' => ['id', 'name', 'email']]);

    // DB has the attachment record (created by the factory above)
    $this->assertDatabaseHas('attachments', [
        'filename' => 'document.pdf',
        'uploaded_by' => $auth->id,
        'disk' => 'public',
    ]);

    // Assert that the file exists on the fake public disk at the path returned by service
    Storage::disk('public')->assertExists($returnedAttachment->path);
});

test('destroy deletes file from disk (if present) and deletes the model', function () {
    Storage::fake('public');

    $auth = User::factory()->create();
    $this->actingAs($auth, 'sanctum');

    // Put a fake file in storage and make an attachment record pointing to it.
    // Include attachable_type/id so factory insert doesn't violate NOT NULL.
    $path = 'attachments/fake-file.txt';
    Storage::disk('public')->put($path, 'hello world');

    $attachment = Attachment::factory()->create([
        'disk' => 'public',
        'path' => $path,
        'attachable_type' => User::class,
        'attachable_id' => $auth->id,
        'uploaded_by' => $auth->id,
    ]);

    // ensure file exists before delete
    Storage::disk('public')->assertExists($path);

    $response = $this->deleteJson(route('attachments.destroy', $attachment));

    $response->assertStatus(204);

    // file should be gone and model deleted
    Storage::disk('public')->assertMissing($attachment->path);
    $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
});