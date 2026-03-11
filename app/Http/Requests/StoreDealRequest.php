<?php

namespace App\Http\Requests;

use App\Models\Deal;
use Illuminate\Foundation\Http\FormRequest;

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
            'title' => 'required|string|max:255',
            'company_id' => 'nullable|integer|exists:companies,id',
            'contact_id' => 'nullable|integer|exists:contacts,id',
            'owner_id' => 'nullable|integer|exists:users,id',
            'pipeline_id' => 'nullable|integer|exists:pipelines,id',
            'stage_id' => 'nullable|integer|exists:pipeline_stages,id',
            'value' => 'nullable|numeric',
            'currency' => 'nullable|string|max:8',
            'close_date' => 'nullable|date',
            'status' => 'nullable|in:open,won,lost,archived',
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
