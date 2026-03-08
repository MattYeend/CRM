<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;
use App\Services\ContactLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Declare a protected property to hold the ContactLogService instance
     *
     * @var ContactLogService
     */
    protected ContactLogService $logger;

    /**
     * Constructor for the controller
     *
     * @param ContactLogService $logger
     *
     * An instance of the ContactLogService used for logging
     * contact-related actions
     */
    public function __construct(ContactLogService $logger)
    {
        $this->logger = $logger;
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

        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );
        $q = $request->query('q');
        $companyId = $request->query('company_id');

        $query = Contact::with('company');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        if ($q) {
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('first_name', 'like', '%' . $q . '%')
                    ->orWhere('last_name', 'like', '%' . $q . '%')
                    ->orWhere('email', 'like', '%' . $q . '%');
            });
        }

        return response()->json($query->paginate($perPage));
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

        return response()->json(
            $contact->load('company', 'deals', 'attachments')
        );
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
        $user = $request->user();
        $data = $request->validated();
        $data['created_by'] = $user->id;

        $contact = Contact::create($data);

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
        $user = $request->user();
        $data = $request->validated();
        $data['updated_by'] = $user->id;

        $contact->update($data);

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

        $contact->update([
            'deleted_by' => $user->id,
        ]);
        $contact->delete();

        return response()->json(null, 204);
    }
}
