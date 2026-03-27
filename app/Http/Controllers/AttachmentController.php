<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Http\Requests\UpdateAttachmentRequest;
use App\Models\Attachment;
use App\Services\Attachments\AttachmentAttacherService;
use App\Services\Attachments\AttachmentLogService;
use App\Services\Attachments\AttachmentManagementService;
use App\Services\Attachments\AttachmentQueryService;
use Illuminate\Http\JsonResponse;

class AttachmentController extends Controller
{
    /**
     * Declare a protected property to hold the
     * AttachmentAttacherService, ActivityLogService,
     * AttachmentQueryService and AttachmentManagementService
     * instances
     *
     * @var AttachmentAttacherService
     * @var AttachmentLogService
     * @var AttachmentQueryService
     * @var AttachmentManagementService
     */
    protected AttachmentAttacherService $attacher;
    protected AttachmentLogService $logger;
    protected AttachmentQueryService $query;
    protected AttachmentManagementService $management;

    /**
     * Constructor for the controller
     *
     * @param AttachmentAttacherService $attacher
     *
     * @param AttachmentLogService $logger
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
     * An instance of the AttacherService for attaching attachments
     */
    public function __construct(
        AttachmentAttacherService $attacher,
        AttachmentLogService $logger,
        AttachmentQueryService $query,
        AttachmentManagementService $management,
    ) {
        $this->attacher = $attacher;
        $this->logger = $logger;
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

        $this->logger->attachmentUploaded(
            $user,
            $user->id,
            $attachment
        );

        return response()->json($attachment->load('uploader'), 201);
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

        $attachment = $this->management->update($request, $attachment);

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

    /**
     * Restore the specified user from soft deletion.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function restore($id): JsonResponse
    {
        $attachment = Attachment::withTrashed()->findOrFail($id);
        $this->authorize('restore', $attachment);

        if (! $attachment->trashed()) {
            abort(404);
        }

        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->attachmentRestored(
            $user,
            $user->id,
            $attachment,
        );

        return response()->json($attachment);
    }
}
