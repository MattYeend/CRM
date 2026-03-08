<?php

namespace App\Http\Requests;

use App\Models\PipelineStage;
use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'pipeline_id' => 'required|integer|exists:pipelines,id',
            'name' => 'required|string',
            'position' => 'nullable|integer',
            'is_won_stage' => 'nullable|boolean',
            'is_lost_stage' => 'nullable|boolean',
        ];
    }
}
