<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Log;
use App\Models\User;

class CompanyLogService
{
    public function __construct()
    {
        // Empty constructor
    }

    /**
     * Log the creation of a company.
     *
     * @param User $user The user that was created.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Company $company The company being logged.
     *
     * @return Log The created log entry.
     */
    public function companyCreated(
        User $user,
        int $userId,
        Company $company
    ): array {
        $data = $this->baseCompanyData($company) + [
            'created_at' => $company->created_at,
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_COMPANY_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the update of a company.
     *
     * @param User $user The user that was updated.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Company $company The company being logged.
     *
     * @return Log The created log entry.
     */
    public function companyUpdated(
        User $user,
        int $userId,
        Company $company
    ): array {
        $data = $this->baseCompanyData($company) + [
            'updated_at' => $company->updated_at,
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_COMPANY_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the deletion of a company.
     *
     * @param User $user The user that was deleted.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Company $company The company being logged.
     *
     * @return Log The created log entry.
     */
    public function companyDeleted(
        User $user,
        int $userId,
        Company $company
    ): array {
        $data = $this->baseCompanyData($company) + [
            'deleted_at' => $company->deleted_at,
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_COMPANY_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the restoration of a company.
     *
     * @param User $user The user that was restored.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Company $company The company being logged.
     *
     * @return Log The created log entry.
     */
    public function companyRestored(
        User $user,
        int $userId,
        Company $company
    ): array {
        $data = $this->baseCompanyData($company) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_COMPANY_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Construct base data array for a Company.
     *
     * @param Company $company The company being logged.
     *
     * @return array The base data for logging.
     */
    protected function baseCompanyData(Company $company): array
    {
        return [
            'id' => $company->id,
            'name' => $company->name,
            'industry' => $company->industry,
            'website' => $company->website,
            'phone' => $company->phone,
            'address' => $company->address,
            'city' => $company->city,
            'region' => $company->region,
            'country' => $company->country,
        ];
    }
}
