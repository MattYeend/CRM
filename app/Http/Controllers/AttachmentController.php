<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Services\AttachmentAttacher;
use App\Services\AttachmentLogService;
use App\Services\AttachmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    protected AttachmentAttacher $attacher;
    protected AttachmentLogService $logger;
    protected AttachmentService $service;

    /**
     * Constructor
     *
     * @param \App\Services\AttachmentAttacher $attacher
     *
     * @param \App\Services\AttachmentLogService $logger
     *
     * @param \App\Services\AttachmentService $service
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
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);

        return response()->json(
            Attachment::with('uploader')->paginate($perPage)
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Attachment $attachment
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Attachment $attachment)
    {
        return response()->json($attachment->load('uploader'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|file|max:10000',
            'attachable_type' => 'nullable|string',
            'attachable_id' => 'nullable|integer',
            'uploaded_by' => 'nullable|integer|exists:users,id',
        ]);

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
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Attachment $attachment
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Attachment $attachment)
    {
        if ($attachment->disk && $attachment->path) {
            Storage::disk($attachment->disk)->delete($attachment->path);
        }
        $attachment->delete();

        $this->logger->attachmentDeleted(
            request()->user(),
            auth()->id(),
            $attachment
        );

        return response()->json(null, 204);
    }

    /**
     * Log the upload of an attachment.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @param \App\Models\Attachment $attachment
     *
     * @return void
     */
    private function logUpload(Request $request, $attachment): void
    {
        $this->logger->attachmentUploaded(
            $request->user(),
            auth()->id(),
            $attachment
        );
    }
}
