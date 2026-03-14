<?php

namespace App\Http\Requests;

use App\Models\PipelineStage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePipelineStageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', PipelineStage::class);
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
            'pipeline_id' => [
                'required',
                'integer',
                Rule::exists('pipelines', 'id'),
            ],
            'name' => 'sometimes|required|string',
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
