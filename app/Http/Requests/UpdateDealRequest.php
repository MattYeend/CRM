<?php

namespace App\Http\Requests;

use App\Models\Deal;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for updating an existing Deal.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — delegates to relationship, core, and status sub-groups
 *   - relationshipBaseRules — company, owner, pipeline, and stage associations
 *   - coreBaseRules — title, value, currency, and close date fields
 *   - statusBaseRules — deal status constrained to allowed values
 *   - metaRules — optional meta payload
 */
class UpdateDealRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound deal and delegates to the 'update' policy.
     *
     * @return bool True if the authenticated user may update this deal.
     */
    public function authorize(): bool
    {
        $deal = $this->route('deal');

        return $this->user()->can('update', $deal);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Merges base and meta rule groups into a single ruleset.
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
     * Aggregate validation rules for all core deal fields.
     *
     * Merges relationship, core, and status sub-groups into a single
     * base ruleset.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
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
     * Aggregate validation rules for all relationship fields.
     *
     * Merges company, owner, and pipeline/stage sub-groups into a single
     * relationship ruleset.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
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
     * Validation rules for the company association.
     *
     * Ensures company_id, when provided, references an existing company
     * record.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
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
     * Validation rules for the owner association.
     *
     * Ensures owner_id, when provided, references an existing user record.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
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
     * Validation rules for the pipeline and stage associations.
     *
     * Ensures pipeline_id and stage_id, when provided, reference existing
     * pipeline and pipeline stage records respectively.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
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
     * Validation rules for core deal fields.
     *
     * All fields are optional on update but constrained to appropriate types
     * and lengths when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function coreBaseRules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'value' => 'nullable|numeric',
            'currency' => 'nullable|string|max:8',
            'close_date' => 'nullable|date',
        ];
    }

    /**
     * Validation rules for the deal status field.
     *
     * Constrains the value to the set of statuses defined on the Deal model.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
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
     * Validation rules for optional metadata fields.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function metaRules(): array
    {
        return [
            'meta' => 'nullable|array',
        ];
    }
}
