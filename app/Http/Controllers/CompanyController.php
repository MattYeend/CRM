<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Services\Companies\CompanyLogService;
use App\Services\Companies\CompanyManagementService;
use App\Services\Companies\CompanyQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Declare a protected property to hold the CompanyLogService,
     * CompanyManagementService and CompanyQueryService instance
     *
     * @var CompanyLogService
     * @var CompanyManagementService
     * @var CompanyQueryService
     */
    protected CompanyLogService $logger;
    protected CompanyManagementService $management;
    protected CompanyQueryService $query;

    /**
     * Constructor for the controller
     *
     * @param CompanyLogService $logger
     *
     * @param CompanyManagementService $management
     *
     * @param CompanyQueryService $query
     *
     * An instance of the CompanyLogService used for logging
     * company-related actions
     * An instance of the CompanyManagementService for management
     * of companies
     * An instance of the CompanyQueryService for the query of
     * company-related actions
     */
    public function __construct(
        CompanyLogService $logger,
        CompanyManagementService $management,
        CompanyQueryService $query,
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
        $this->authorize('viewAny', Company::class);

        $company = $this->query->list($request);

        return response()->json($company);
    }

    /**
     * Display the specified resource.
     *
     * @param Company $company
     *
     * @return JsonResponse
     */
    public function show(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        $company = $this->query->show($company);

        return response()->json($company);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCompanyRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $company = $this->management->store($request);

        $user = $request->user();

        $this->logger->companyCreated(
            $user,
            $user->id,
            $company,
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
     * @return JsonResponse
     */
    public function update(
        UpdateCompanyRequest $request,
        Company $company
    ): JsonResponse {
        $company = $this->management->update($request, $company);

        $user = $request->user();

        $this->logger->companyUpdated(
            $user,
            $user->id,
            $company,
        );

        return response()->json($company);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Company $company
     *
     * @return JsonResponse
     */
    public function destroy(Company $company): JsonResponse
    {
        $this->authorize('delete', $company);

        $user = auth()->user();

        $this->logger->companyDeleted(
            $user,
            $user->id,
            $company,
        );

        $this->management->destroy($company);

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
        $company = Company::withTrashed()->findOrFail($id);
        $this->authorize('restore', $company);
        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->companyRestored(
            $user,
            $user->id,
            $company
        );

        return response()->json($company);
    }
}
