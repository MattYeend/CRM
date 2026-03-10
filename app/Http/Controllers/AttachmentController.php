<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Http\Requests\UpdateAttachmentRequest;
use App\Models\Attachment;
use App\Services\AttachmentAttacherService;
use App\Services\AttachmentLogService;
use App\Services\AttachmentManagementService;
use App\Services\AttachmentQueryService;
use App\Services\AttachmentService;
use Illuminate\Http\JsonResponse;

class AttachmentController extends Controller
{
    /**
     * Declare a protected property to hold the
     * AttachmentAttacherService, ActivityLogService,
     * AttachmentService and AttachmentManagementService
     * instances
     *
     * @var AttachmentAttacherService
     * @var AttachmentLogService
     * @var AttachmentService
     * @var AttachmentQueryService
     * @var AttachmentManagementService
     */
    protected AttachmentAttacherService $attacher;
    protected AttachmentLogService $logger;
    protected AttachmentService $service;
    protected AttachmentQueryService $query;
    protected AttachmentManagementService $management;

    /**
     * Constructor for the controller
     *
     * @param AttachmentAttacherService $attacher
     *
     * @param AttachmentLogService $logger
     *
     * @param AttachmentService $service
     *
     * @param AttachmentQueryService $query
     *
     * @param AttachmentManagementService $management
     *
     * An instance of the AttachmentLogService used for logging
     * activitattachmenty-related actions
     * An instance of the AttachmentManagementService for management
     * of attachments
     * An instance of the AttachmentQueryService for the query of
     * attachment-related actions
     */
    public function __construct(
        AttachmentAttacherService $attacher,
        AttachmentLogService $logger,
        AttachmentService $service,
        AttachmentQueryService $query,
        AttachmentManagementService $management,
    ) {
        $this->attacher = $attacher;
        $this->logger = $logger;
        $this->service = $service;
        $this->query = $query;
        $this->management = $management;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Attachment::class);

        $attachment = $this->query->list(request());

        return response()->json($attachment);
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

        $attachment = $this->query->show($attachment);

        return response()->json($attachment);
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

        $attachment = $this->management->store($request);

        $attachment = $this->service->storeFile(
            $request->file('file'),
            $request->user()->id
        );
        $validated = $request->validated();
        $this->attacher->attach(
            $validated['attachable_type'] ?? null,
            $validated['attachable_id'] ?? null,
            $attachment
        );

        $this->logger->attachmentUploaded(
            $user,
            $user->id,
            $attachment
        );

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

        $file = $request->file('file');

        if ($file) {
            $attachment = $this->service->replaceFile(
                $attachment,
                $file,
                $user->id
            );
        }

        $this->attacher->attach(
            $request->input('attachable_type'),
            $request->input('attachable_id'),
            $attachment
        );

        $this->logger->attachmentUploaded(
            $user,
            $user->id,
            $attachment,
        );

        return response()->json($attachment->load('uploader'), 200);
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
            $attachment,
        );

        $this->management->destroy($attachment, $user->id);

        return response()->json(null, 204);
    }
}
