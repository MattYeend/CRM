<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for storing a new Order.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — delegates to relationship, core, and status sub-groups
 *   - relationshipBaseRules — required deal and user associations
 *   - coreBaseRules — amount, currency, and payment detail fields
 *   - statusBaseRules — order status constrained to allowed values
 *   - metaRules — optional meta payload
 */
class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the Order model.
     *
     * @return bool True if the authenticated user may create orders.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Order::class);
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
     * Aggregate validation rules for all core order fields.
     *
     * Merges relationship, core, and status sub-groups into a single
     * base ruleset.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return array_merge(
            $this->relationshipBaseRules(),
            $this->coreBaseRules(),
            $this->statusBaseRules(),
        );
    }

    /**
     * Validation rules for required deal and user associations.
     *
     * Ensures both deal_id and user_id reference existing records.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function relationshipBaseRules(): array
    {
        return [
            'deal_id' => [
                'required',
                'integer',
                Rule::exists('deals', 'id'),
            ],
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
        ];
    }

    /**
     * Validation rules for core order financial and payment fields.
     *
     * Amount and currency are required; payment method, date, and
     * gateway reference fields are optional.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function coreBaseRules(): array
    {
        return [
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'payment_method' => 'nullable|string|max:50',
            'paid_at' => 'nullable|date',
            'payment_intent_id' => 'nullable|string|max:255',
            'charge_id' => 'nullable|string|max:255',
        ];
    }

    /**
     * Validation rules for the order status field.
     *
     * Constrains the value to the set of statuses defined on the Order model.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function statusBaseRules(): array
    {
        return [
            'status' => [
                'nullable',
                Rule::in([
                    Order::STATUS_PENDING,
                    Order::STATUS_PAID,
                    Order::STATUS_FAILED,
                ]),
            ],
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
