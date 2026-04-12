<?php

namespace App\Services\Attachments;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Handles read queries for Attachment records.
 *
 * Delegates sorting and trash filtering to dedicated sub-services and
 * returns paginated or single attachment results with the appropriate
 * relationships loaded.
 */
class AttachmentQueryService
{
    /**
     * Service responsible for applying sort order to attachment queries.
     *
     * @var AttachmentSortingService
     */
    private AttachmentSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters to attachment
     * queries.
     *
     * @var AttachmentTrashFilterService
     */
    private AttachmentTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  AttachmentSortingService $sorting Handles sort order application.
     * @param  AttachmentTrashFilterService $trashFilter Handles trash
     * visibility filtering.
     */
    public function __construct(
        AttachmentSortingService $sorting,
        AttachmentTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of attachments with sorting and trash filters
     * applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry sort, filter,
     * and pagination params.
     *
     * @return array Paginated attachment results.
     */
    public function list(Request $request): array
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = Attachment::with('uploader');

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $query->paginate($perPage)->appends($request->query());

        $paginator->through(
            fn (Attachment $attachment) => $this->formatAttachment($attachment)
        );

        $result = $paginator->toArray();

        $result['permissions'] = [
            'create' => Gate::allows('create', Attachment::class),
            'viewAny' => Gate::allows('viewAny', Attachment::class),
        ];

        return $result;
    }

    /**
     * Return a single attachment with the uploader relationship loaded.
     *
     * @param  Attachment $attachment The route-model-bound attachment instance.
     *
     * @return Attachment The attachment with uploader loaded.
     */
    public function show(Attachment $attachment): Attachment
    {
        return $attachment->load('uploader');
    }

    /**
     * Format a attachment into a structured array.
     *
     * Includes core attributes, related user data, derived subject name,
     * and authorisation permissions for the current user.
     *
     * @param  Attachment $attchment
     *
     * @return array
     */
    private function formatAttachment(Attachment $attachment): array
    {
        return [
            'id' => $attachment->id,
            'filename' => $attachment->filename,
            'disk' => $attachment->disk,
            'path' => $attachment->path,
            'attachable_type' => $attachment->attachable_type,
            'attachable_id' => $attachment->attachable_id,
            'attachable_type' => $attachment->attachable_type,
            'uploaded_by' => $attachment->uploader?->name,
            'size' => $attachment->size,
            'created_at' => $attachment->created_at,
            'mime' => $attachment->mime,
            'creator' => $attachment->creator,
            'permissions' => [
                'view' => Gate::allows('view', $attachment),
                'update' => Gate::allows('update', $attachment),
                'delete' => Gate::allows('delete', $attachment),
            ],
        ];
    }
}
