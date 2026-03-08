<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'pipeline_id' => 'nullable|integer|exists:pipelines,id',
            'name' => 'sometimes|required|string',
            'position' => 'nullable|integer',
            'is_won_stage' => 'nullable|boolean',
            'is_lost_stage' => 'nullable|boolean',
        ];
    }
}
