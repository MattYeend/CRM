<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for updating an existing Quote.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — deal association, financial totals, and date fields
 *   - metaRules — optional metadata payload
 */
class UpdateQuoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound quote and delegates to the 'update' policy.
     *
     * @return bool True if the authenticated user may update this quote.
     */
    public function authorize(): bool
    {
        $quote = $this->route('quote');

        return $this->user()->can('update', $quote);
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
     * Ensures deal_id references an existing deal and remains unique across
     * quotes when provided, excluding the current model being updated.
     * Financial and date fields are optional but must conform to valid types.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        $quote = $this->route('quote');

        return [
            'deal_id' => [
                'sometimes',
                'integer',
                'exists:deals,id',
                Rule::unique('quotes', 'deal_id')->ignore($quote),
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
