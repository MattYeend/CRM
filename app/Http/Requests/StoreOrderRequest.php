<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Order::class);
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
        return array_merge(
            $this->relationshipBaseRules(),
            $this->coreBaseRules(),
            $this->statusBaseRules(),
        );
    }

    /**
     * Relationship base rules
     *
     * @return array
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
     * Core base rules
     *
     * @return array
     */
    private function coreBaseRules(): array
    {
        return [
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'payment_method' => 'nullable|string|max:50',
            'paid_at' => 'nullable|date',
            'payment_intent_id' => 'nullable|string|max:255',
            'charge_id' => 'nullable|string|max:255',
        ];
    }

    /**
     * Status base rules
     *
     * @return array
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
