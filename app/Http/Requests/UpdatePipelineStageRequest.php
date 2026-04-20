<?php

namespace App\Http\Requests;

use App\Models\PipelineStage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for updating an existing PipelineStage.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — optional pipeline association and stage name
 *   - metaRules — position, won stage flag, and lost stage flag
 */
class UpdatePipelineStageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound pipeline stage and delegates to the
     * 'update' policy.
     *
     * @return bool True if the authenticated user may update this pipeline
     * stage.
     */
    public function authorize(): bool
    {
        $pipelineStage = $this->route('pipeline_stage');

        return $this->user()->can('update', $pipelineStage);
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
     * Ensures pipeline_id references an existing pipeline when provided,
     * and that the name is a valid string when present.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'pipeline_id' => [
                'nullable',
                'integer',
                Rule::exists('pipelines', 'id'),
            ],
            'deal_id' => [
                'nullable',
                'integer',
                Rule::exists('deals', 'id'),
            ],
            'name' => 'sometimes|string',
        ];
    }

    /**
     * Validation rules for stage positioning and outcome flag fields.
     *
     * Won and lost stage flags are constrained to their respective allowed
     * values defined on the PipelineStage model.
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
