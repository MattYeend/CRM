<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Services\CompanyLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Declare a protected property to hold the CompanyLogService instance
     *
     * @var CompanyLogService
     */
    protected CompanyLogService $logger;

    /**
     * Constructor for the controller
     *
     * @param CompanyLogService $logger
     * An instance of the CompanyLogService used for logging
     * company-related actions
     */
    public function __construct(CompanyLogService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Company::class);

        $perPage = max(1, min((int) $request->query('per_page', 10), 100));
        $q = $request->query('q');

        $query = Company::query()
            ->withCount(['contacts', 'deals', 'invoices']);

        if ($q) {
            $query->where('name', 'like', "%{$q}%");
        }

        return response()->json(
            $query->paginate($perPage)
        );
    }

    /**
     * Display the specified resource.
     *
     * @param Company $company
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Company $company): JsonResponse
    {
        $this->authorize('view', $company);
        return response()->json(
            $company->load(
                'contacts',
                'deals',
                'invoices',
                'attachments'
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCompanyRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $data['created_by'] = $user->id;

        $company = Company::create($data);

        $this->logger->companyCreated(
            $user,
            $user->id,
            $company
        );

        return response()->json($company, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCompanyRequest $request
     *
     * @param Company $company
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(
        UpdateCompanyRequest $request,
        Company $company
    ): JsonResponse {
        $user = $request->user();
        $data = $request->validated();
        $data['updated_by'] = $user->id;

        $company->update($data);

        $this->logger->companyUpdated(
            $request->user(),
            auth()->id(),
            $company
        );

        return response()->json($company);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Company $company
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Company $company): JsonResponse
    {
        $this->authorize('delete', $company);

        $user = auth()->user();

        $this->logger->companyDeleted(
            $user,
            $user->id,
            $company
        );

        $company['deleted_by'] = $user->id;
        $company->save();
        $company->delete();

        return response()->json(null, 204);
    }

    /**
     * Restore the specified resource from soft deletion.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id): JsonResponse
    {
        $company = Company::withTrashed()->findOrFail($id);

        $this->authorize('restore', $company);

        $company->restore();

        $this->logger->companyRestored(
            request()->user(),
            auth()->id(),
            $company
        );

        return response()->json($company);
    }
}
