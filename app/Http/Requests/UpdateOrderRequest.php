<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for updating an existing Order.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — aggregates relationship, core, and status rule groups
 *   - relationshipBaseRules — optional order and user associations
 *   - coreBaseRules — financial and payment-related fields
 *   - statusBaseRules — order status constrained to allowed values
 *   - metaRules — optional metadata payload
 */
class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound order and delegates to the 'update' policy.
     *
     * @return bool True if the authenticated user may update this order.
     */
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $this->user()->can('update', $order);
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
     * Aggregate validation rules for all updatable order fields.
     *
     * Combines relationship, core, and status rule groups into a single
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
     * Validation rules for optional order and user associations.
     *
     * Ensures provided IDs reference existing records when present.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function relationshipBaseRules(): array
    {
        return [
            'assigned_to' => [
                'sometimes',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'order_id' => [
                'sometimes',
                'integer',
                Rule::exists('orders', 'id'),
            ],
        ];
    }

    /**
     * Validation rules for core financial and payment fields.
     *
     * Amount and currency are optional; payment method, timestamps,
     * and gateway reference fields may be provided if available.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function coreBaseRules(): array
    {
        return [
            'amount' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'payment_method' => 'nullable|string|max:50',
            'paid_at' => 'nullable|date',
            'payment_intent_id' => 'nullable|string|max:255',
            'charge_id' => 'nullable|string|max:255',
        ];
    }

    /**
     * Validation rules for the order status field.
     *
     * Restricts the value to the set of statuses defined on the Order model.
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
