<?php

namespace App\Http\Requests;

use App\Models\Deal;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDealRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Deal::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            $this->metaRules(),
        );
    }

    /**
     * Base rules
     *
     * @return array
     */
    private function baseRules(): array
    {
        return array_merge(
            $this->relationshipBaseRules(),
            $this->coreBaseRules(),
            $this->statusBaseRules(),
        );
    }

    /**
     * Relationship base rules
     *
     * @return array
     */
    private function relationshipBaseRules(): array
    {
        return array_merge(
            $this->companyRelationshipRules(),
            $this->ownerRelationshipRules(),
            $this->pipelineAndStagesRelationshipRules(),
        );
    }

    /**
     * Company relationship rules
     *
     * @return array
     */
    private function companyRelationshipRules(): array
    {
        return [
            'company_id' => [
                'nullable',
                'integer',
                Rule::exists('companies', 'id'),
            ],
        ];
    }

    /**
     * User/Owner relationship rules
     *
     * @return array
     */
    private function ownerRelationshipRules(): array
    {
        return [
            'owner_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ],
        ];
    }

    /**
     * Pipeline and Stages relationship rules
     *
     * @return array
     */
    private function pipelineAndStagesRelationshipRules(): array
    {
        return [
            'pipeline_id' => [
                'nullable',
                'integer',
                Rule::exists('pipelines', 'id'),
            ],
            'stage_id' => [
                'nullable',
                'integer',
                Rule::exists('pipeline_stages', 'id'),
            ],
        ];
    }

    /**
     * Core base rules
     *
     * @return array
     */
    private function coreBaseRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'value' => 'nullable|numeric',
            'currency' => 'nullable|string|max:8',
            'close_date' => 'nullable|date',
        ];
    }

    /**
     * Status base rules
     *
     * @return array
     */
    private function statusBaseRules(): array
    {
        return [
            'status' => [
                'nullable',
                Rule::in([
                    Deal::STATUS_OPEN,
                    Deal::STATUS_WON,
                    Deal::STATUS_LOST,
                    Deal::STATUS_ARCHIVED,
                ]),
            ],
        ];
    }

    /**
     * Meta rules
     *
     * @return array
     */
    private function metaRules(): array
    {
        return [
            'meta' => 'nullable|array',
        ];
    }
}
