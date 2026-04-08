<?php

namespace App\Services\Companies;

use App\Models\Company;
use App\Models\Log;
use App\Models\User;

/**
 * Handles logging of Company-related actions.
 *
 * Provides methods to log creation, update, deletion, and restoration
 * of company records, recording the responsible user and timestamps.
 */
class CompanyLogService
{
    /**
     * Log the creation of a company.
     *
     * Records the user who created the company and the timestamp.
     *
     * @param  User $user The user performing the action.
     * @param  int $userId The ID of the user performing the action.
     * @param  Company $company The company being created.
     *
     * @return array The logged data for the creation action.
     */
    public function companyCreated(
        User $user,
        int $userId,
        Company $company
    ): array {
        $data = $this->baseCompanyData($company) + [
            'created_at' => now(),
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
     * Records the user who updated the company and the timestamp.
     *
     * @param  User $user The user performing the action.
     * @param  int $userId The ID of the user performing the action.
     * @param  Company $company The company being updated.
     *
     * @return array The logged data for the update action.
     */
    public function companyUpdated(
        User $user,
        int $userId,
        Company $company
    ): array {
        $data = $this->baseCompanyData($company) + [
            'updated_at' => now(),
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
     * Records the user who deleted the company and the timestamp.
     *
     * @param  User $user The user performing the action.
     * @param  int $userId The ID of the user performing the action.
     * @param  Company $company The company being deleted.
     *
     * @return array The logged data for the deletion action.
     */
    public function companyDeleted(
        User $user,
        int $userId,
        Company $company
    ): array {
        $data = $this->baseCompanyData($company) + [
            'deleted_at' => now(),
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
     * Records the user who restored the company and the timestamp.
     *
     * @param  User $user The user performing the action.
     * @param  int $userId The ID of the user performing the action.
     * @param  Company $company The company being restored.
     *
     * @return array The logged data for the restoration action.
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
     * Prepare the base data for logging a company.
     *
     * Extracts relevant attributes from the company for logging purposes.
     *
     * @param  Company $company The company to extract data from.
     *
     * @return array The base data array to be included in logs.
     */
    protected function baseCompanyData(Company $company): array
    {
        return [
            'id' => $company->id,
            'name' => $company->name,
            'industry id' => $company->industry_id,
            'website' => $company->website,
            'phone' => $company->phone,
            'address' => $company->address,
            'city' => $company->city,
            'region' => $company->region,
            'country' => $company->country,
        ];
    }
}
