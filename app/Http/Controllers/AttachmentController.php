<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Http\Requests\UpdateAttachmentRequest;
use App\Models\Attachment;
use App\Services\AttachmentAttacher;
use App\Services\AttachmentLogService;
use App\Services\AttachmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * Declare a protected property to hold the
     * AttachmentAttacher, ActivityLogService and
     * AttachmentService instances
     *
     * @var AttachmentAttacher
     * @var AttachmentLogService
     * @var AttachmentService
     */
    protected AttachmentAttacher $attacher;
    protected AttachmentLogService $logger;
    protected AttachmentService $service;

    /**
     * Constructor
     *
     * @param AttachmentAttacher $attacher
     *
     * @param AttachmentLogService $logger
     *
     * @param AttachmentService $service
     *
     * @return void
     */
    public function __construct(
        AttachmentAttacher $attacher,
        AttachmentLogService $logger,
        AttachmentService $service
    ) {
        $this->attacher = $attacher;
        $this->logger = $logger;
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Attachment::class);

        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        return response()->json(
            Attachment::with('uploader')->paginate($perPage)
        );
    }

    /**
     * Display the specified resource.
     *
     * @param Attachment $attachment
     *
     * @return JsonResponse
     */
    public function show(Attachment $attachment): JsonResponse
    {
        $this->authorize('view', $attachment);

        return response()->json($attachment->load('uploader'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreAttachmentRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreAttachmentRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;

        $file = $request->file('file');
        $attachment = $this->service->storeFile(
            $file,
            $data['uploaded_by'] ?? null
        );

        $this->attacher->attach(
            $data['attachable_type'] ?? null,
            $data['attachable_id'] ?? null,
            $attachment
        );

        $this->logUpload($request, $attachment);

        return response()->json($attachment->load('uploader'), 201);
    }

    /**
     * Update the specified resource.
     *
     * @param UpdateAttachmentRequest $request
     *
     * @param Attachment $attachment
     *
     * @return JsonResponse
     */
    public function update(
        UpdateAttachmentRequest $request,
        Attachment $attachment
    ): JsonResponse {
        $user = $request->user();
        $data = $request->validated();

        $data['updated_by'] = $user->id;

        $file = $request->file('file');

        if ($file) {
            $this->service->replaceFile($attachment, $file);
        }

        $this->attacher->attach(
            $data['attachable_type'] ?? null,
            $data['attachable_id'] ?? null,
            $attachment
        );

        $this->logUpload($request, $attachment);

        return response()->json($attachment->load('uploader'), 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Attachment $attachment
     *
     * @return JsonResponse
     */
    public function destroy(Attachment $attachment): JsonResponse
    {
        $this->authorize('delete', $attachment);

        $user = auth()->user();

        $this->logger->attachmentDeleted(
            $user,
            $user->id,
            $attachment
        );

        if ($attachment->disk && $attachment->path) {
            Storage::disk($attachment->disk)->delete($attachment->path);
        }

        $attachment->update([
            'deleted_by' => $user->id,
        ]);
        $attachment->delete();

        return response()->json(null, 204);
    }

    /**
     * Log the upload of an attachment.
     *
     * @param $request
     *
     * @param Attachment $attachment
     *
     * @return void
     */
    private function logUpload($request, $attachment): void
    {
        $user = $request->user();

        $this->logger->attachmentUploaded(
            $user,
            $user->id,
            $attachment
        );
    }
}
