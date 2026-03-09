<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;
use App\Services\ContactLogService;
use App\Services\ContactManagementService;
use App\Services\ContactQueryService;
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
    protected ContactManagementService $managementService;
    protected ContactQueryService $queryService;

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
     * company-related actions
     * An instance of the ContactManagementService for management
     * of companies
     * An instance of the ContactQueryService for the query of
     * company-related actions
     */
    public function __construct(
        ContactLogService $logger,
        ContactManagementService $managementService,
        ContactQueryService $queryService,
    ) {
        $this->logger = $logger;
        $this->managementService = $managementService;
        $this->queryService = $queryService;
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

        $contact = $this->queryService->list($request);

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

        $contact = $this->queryService->show($contact);

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
        $contact = $this->managementService->store($request);

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
        $contact = $this->managementService->update($request, $contact);

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

        $this->managementService->destroy($contact);

        return response()->json(null, 204);
    }
}
