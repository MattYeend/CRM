<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Services\Companies\CompanyLogService;
use App\Services\Companies\CompanyManagementService;
use App\Services\Companies\CompanyQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the Company resource.
 *
 * Delegates business logic to three dedicated services:
 *   - CompanyLogService — records audit log entries for company changes
 *   - CompanyManagementService — handles create, update, delete, and
 *      restore operations
 *   - CompanyQueryService — handles read/list queries with filtering and
 *      pagination
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class CompanyController extends Controller
{
    /**
     * Service responsible for writing audit log entries for company events.
     *
     * @var CompanyLogService
     */
    protected CompanyLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * companies.
     *
     * @var CompanyManagementService
     */
    protected CompanyManagementService $management;

    /**
     * Service responsible for querying and listing companies.
     *
     * @var CompanyQueryService
     */
    protected CompanyQueryService $query;

    /**
     * Inject the required services into the controller.
     *
     * @param  CompanyLogService $logger Handles audit logging for
     * company events.
     * @param  CompanyManagementService $management Handles company
     * create/update/delete/restore.
     * @param  CompanyQueryService $query Handles company listing and
     * retrieval.
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
     * Also includes the authenticated user's permissions for the Company
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated company data with pagination metadata and
     * permissions.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Company::class);

        $companies = $this->query->list($request);

        return response()->json($companies);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreCompanyRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param  StoreCompanyRequest $request Validated request containing
     * company data.
     *
     * @return JsonResponse The newly created company, with HTTP 201 Created.
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
     * Display the specified resource.
     *
     * Return a single company by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  Company $company Route-model-bound company instance.
     *
     * @return JsonResponse The resolved company resource.
     */
    public function show(Company $company): JsonResponse
    {
        $this->authorize('view', $company);
        $this->authorize('access', $company);

        $company = $this->query->show($company);

        return response()->json($company);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateCompanyRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     *
     * @param  UpdateCompanyRequest $request Validated request containing
     * updated company data.
     * @param  Company $company Route-model-bound company instance
     * to update.
     *
     * @return JsonResponse The updated company resource.
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
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * company instance is still fully accessible during logging.
     *
     * @param  Company $company Route-model-bound company instance to
     * delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
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
     * Restore the specified user from soft deletion.
     *
     * Looks up the company including trashed records, then authorises via
     * the 'restore' policy. Returns 404 if the company is not currently
     * soft-deleted, preventing accidental double-restores.
     *
     * @param  int|string $id The primary key of the soft-deleted company.
     *
     * @return JsonResponse The restored company resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the company is not trashed (404).
     */
    public function restore($id): JsonResponse
    {
        $company = Company::withTrashed()->findOrFail($id);
        $this->authorize('restore', $company);

        if (! $company->trashed()) {
            abort(404);
        }

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
