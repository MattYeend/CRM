<?php

namespace App\Http\Requests;

use App\Models\Deal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDealRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $deal = $this->route('deal');

        return $this->user()->can('update', $deal);
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
            'title' => 'sometimes|string|max:255',
            'company_id' => 'nullable|integer|exists:companies,id',
            'contact_id' => 'nullable|integer|exists:contacts,id',
            'owner_id' => 'nullable|integer|exists:users,id',
            'pipeline_id' => 'nullable|integer|exists:pipelines,id',
            'stage_id' => 'nullable|integer|exists:pipeline_stages,id',
            'value' => 'nullable|numeric',
            'currency' => 'nullable|string|max:8',
            'close_date' => 'nullable|date',
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
