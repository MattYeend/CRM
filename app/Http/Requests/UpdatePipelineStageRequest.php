<?php

namespace App\Http\Requests;

use App\Models\PipelineStage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePipelineStageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $pipelineStage = $this->route('pipeline_stage');

        return $this->user()->can('update', $pipelineStage);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
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
        return [
            'pipeline_id' => 'nullable|integer|exists:pipelines,id',
            'name' => 'sometimes|string',
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
