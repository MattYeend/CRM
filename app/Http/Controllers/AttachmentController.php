<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Http\Requests\UpdateAttachmentRequest;
use App\Models\Attachment;
use App\Services\Attachments\AttachmentAttacherService;
use App\Services\Attachments\AttachmentLogService;
use App\Services\Attachments\AttachmentManagementService;
use App\Services\Attachments\AttachmentQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the Attachment resource.
 *
 * Delegates business logic to four dedicated services:
 *   - AttachmentAttacherService — associates attachments with
 *      their parent entities
 *   - AttachmentLogService — records audit log entries for
 *      attachment events
 *   - AttachmentManagementService — handles store, update, delete,
 *      and restore operations
 *   - AttachmentQueryService — handles read/list queries
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class AttachmentController extends Controller
{
    /**
     * Service responsible for associating attachments with their
     * parent entities.
     *
     * @var AttachmentAttacherService
     */
    protected AttachmentAttacherService $attacher;

    /**
     * Service responsible for writing audit log entries for attachment events.
     *
     * @var AttachmentLogService
     */
    protected AttachmentLogService $logger;

    /**
     * Service responsible for querying and listing attachments.
     *
     * @var AttachmentQueryService
     */
    protected AttachmentQueryService $query;

    /**
     * Service responsible for creating, updating, deleting,
     * and restoring attachments.
     *
     * @var AttachmentManagementService
     */
    protected AttachmentManagementService $management;

    /**
     * Inject the required services into the controller.
     *
     * @param  AttachmentAttacherService $attacher Handles associating
     * attachments to parent entities.
     * @param  AttachmentLogService $logger Handles audit logging for
     * attachment events.
     * @param  AttachmentQueryService $query Handles attachment listing
     * and retrieval.
     * @param  AttachmentManagementService $management Handles attachment
     * store/update/delete/restore.
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
     * Also includes the authenticated user's permissions for the Attachment
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated attachment data with pagination metadata
     * and permissions.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Attachment::class);

        $paginator = $this->query->list($request);

        return response()->json($paginator);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreAttachmentRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     *
     * The response eager-loads the 'uploader' relationship so the caller
     * immediately has access to the associated user details.
     *
     * @param  StoreAttachmentRequest $request Validated request containing the
     * file and metadata.
     *
     * @return JsonResponse The newly created attachment with its uploader,
     * HTTP 201 Created.
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
     * Return a single attacthment by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  Attachment $attachment Route-model-bound attachment instance.
     *
     * @return JsonResponse The resolved attachment resource.
     */
    public function show(Attachment $attachment): JsonResponse
    {
        $this->authorize('view', $attachment);
        $this->authorize('access', $attachment);

        $attachment = $this->query->show($attachment);

        return response()->json($attachment);
    }

    /**
     * Update the specified resource.
     *
     * Validation is handled upstream by UpdateAttachmentRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the
     * authenticated user.
     *
     * The response eager-loads the 'uploader' relationship for consistency
     * with store().
     *
     * @param  UpdateAttachmentRequest $request Validated request containing
     * updated attachment data.
     * @param  Attachment $attachment Route-model-bound attachment
     * instance to update.
     *
     * @return JsonResponse The updated attachment with its uploader,
     * HTTP 200 OK.
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
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * attachment instance is still fully accessible during logging.
     *
     * The user ID is passed to the management service to record who performed
     * the deletion.
     *
     * @param  Attachment $attachment Route-model-bound attachment instance
     * to delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
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
     * Looks up the attachment including trashed records, then authorises via
     * the 'restore' policy. Returns 404 if the attachment is not currently
     * soft-deleted, preventing accidental double-restores.
     *
     * After restoring, an audit log entry is written against the authenticated
     * user.
     *
     * @param  int|string $id The primary key of the soft-deleted attachment.
     *
     * @return JsonResponse The restored attachment resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the attachment is not trashed (404).
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
