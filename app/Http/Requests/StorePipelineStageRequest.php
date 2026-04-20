<?php

namespace App\Http\Requests;

use App\Models\PipelineStage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for storing a new PipelineStage.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — required pipeline association and stage name
 *   - metaRules — position, won stage flag, and lost stage flag
 */
class StorePipelineStageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the PipelineStage model.
     *
     * @return bool True if the authenticated user may create pipeline stages.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', PipelineStage::class);
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
     * Validation rules for the pipeline association and stage name.
     *
     * Ensures pipeline_id references an existing pipeline record and that
     * a name is present when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'pipeline_id' => [
                'required',
                'integer',
                Rule::exists('pipelines', 'id'),
            ],
            'deal_id' => [
                'nullable',
                'integer',
                Rule::exists('deals', 'id'),
            ],
            'name' => 'sometimes|required|string',
        ];
    }

    /**
     * Validation rules for stage positioning and outcome flag fields.
     *
     * Won and lost stage flags are each constrained to their respective
     * allowed values defined on the PipelineStage model.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function metaRules(): array
    {
        return [
            'position' => 'nullable|integer',
            'is_won_stage' => [
                'nullable',
                Rule::in([
                    PipelineStage::IS_WON_STAGE,
                    PipelineStage::NOT_WON_STAGE,
                ]),
            ],
            'is_lost_stage' => [
                'nullable',
                Rule::in([
                    PipelineStage::IS_LOST_STAGE,
                    PipelineStage::NOT_LOST_STAGE,
                ]),
            ],
        ];
    }
}
