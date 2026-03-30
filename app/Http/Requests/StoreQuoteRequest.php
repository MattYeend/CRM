<?php

namespace App\Http\Requests;

use App\Models\Quote;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for storing a new Quote.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — deal association, financial totals, and date fields
 *   - metaRules — optional meta payload
 */
class StoreQuoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the Quote model.
     *
     * @return bool True if the authenticated user may create quotes.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Quote::class);
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
     * Validation rules for core quote fields.
     *
     * The deal_id must reference an existing deal and be unique across all
     * quotes, enforcing a one-to-one relationship. Financial and date fields
     * are optional but constrained to appropriate types when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'deal_id' => [
                'required',
                'integer',
                Rule::exists('deals', 'id'),
                Rule::unique('quotes', 'deal_id'),
            ],
            'currency' => 'nullable|string|size:3',
            'subtotal' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'total' => 'nullable|numeric',
            'sent_at' => 'nullable|date',
            'accepted_at' => 'nullable|date',
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
