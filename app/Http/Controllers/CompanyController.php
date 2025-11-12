<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\CompanyLogService;
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
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $q = $request->query('q');

        $query = Company::query()->withCount(['contacts', 'deals', 'invoices']);

        if ($q) {
            $query->where('name', 'like', '%' . $q . '%');
        }

        return response()->json($query->paginate($perPage));
    }

    /**
     * Display the specified resource.
     *
     * @param Company $company
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Company $company)
    {
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
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'industry' => 'nullable|string',
            'website' => 'nullable|url',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'region' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'country' => 'nullable|string',
            'meta' => 'nullable|array',
        ]);

        $company = Company::create($data);

        $this->logger->companyCreated(
            $request->user(),
            auth()->id(),
            $company
        );

        return response()->json($company, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     *
     * @param Company $company
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string',
            'industry' => 'nullable|string',
            'website' => 'nullable|url',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'region' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'country' => 'nullable|string',
            'meta' => 'nullable|array',
        ]);

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
    public function destroy(Company $company)
    {
        $company->delete();

        $this->logger->companyDeleted(
            request()->user(),
            auth()->id(),
            $company
        );

        return response()->json(null, 204);
    }

    /**
     * Restore the specified resource from soft deletion.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        $company = Company::withTrashed()->findOrFail($id);

        $company->restore();

        $this->logger->companyRestored(
            request()->user(),
            auth()->id(),
            $company
        );

        return response()->json($company);
    }
}
