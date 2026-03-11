<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;
use App\Services\Contacts\ContactLogService;
use App\Services\Contacts\ContactManagementService;
use App\Services\Contacts\ContactQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Declare a protected property to hold the ContactLogService,
     * ContactManagementService and ContactQueryService instance
     *
     * @var ContactLogService
     * @var ContactManagementService
     * @var ContactQueryService
     */
    protected ContactLogService $logger;
    protected ContactManagementService $management;
    protected ContactQueryService $query;

    /**
     * Constructor for the controller
     *
     * @param ContactLogService $logger
     *
     * @param ContactManagementService $management
     *
     * @param ContactQueryService $query
     *
     * An instance of the ContactLogService used for logging
     * contact-related actions
     * An instance of the ContactManagementService for management
     * of contacts
     * An instance of the ContactQueryService for the query of
     * contact-related actions
     */
    public function __construct(
        ContactLogService $logger,
        ContactManagementService $management,
        ContactQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
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
        $this->authorize('viewAny', Contact::class);

        $contact = $this->query->list($request);

        return response()->json($contact);
    }

    /**
     * Display the specified resource.
     *
     * @param Contact $contact
     *
     * @return JsonResponse
     */
    public function show(Contact $contact): JsonResponse
    {
        $this->authorize('view', $contact);

        $contact = $this->query->show($contact);

        return response()->json($contact);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreContactRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        $contact = $this->management->store($request);

        $user = $request->user();

        $this->logger->contactCreated(
            $user,
            $user->id,
            $contact,
        );

        return response()->json($contact, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateContactRequest $request
     *
     * @param Contact $contact
     *
     * @return JsonResponse
     */
    public function update(
        UpdateContactRequest $request,
        Contact $contact
    ): JsonResponse {
        $contact = $this->management->update($request, $contact);

        $user = $request->user();

        $this->logger->contactUpdated(
            $user,
            $user->id,
            $contact,
        );

        return response()->json($contact);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Contact $contact
     */
    public function destroy(Contact $contact): JsonResponse
    {
        $this->authorize('delete', $contact);

        $user = auth()->user();

        $this->logger->contactDeleted(
            $user,
            $user->id,
            $contact,
        );

        $this->management->destroy($contact);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified resource from soft deletion.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function restore($id): JsonResponse
    {
        $contact = Contact::withTrashed()->findOrFail($id);
        $this->authorize('restore', $contact);
        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->contactRestored(
            $user,
            $user->id,
            $contact
        );

        return response()->json($contact);
    }
}
