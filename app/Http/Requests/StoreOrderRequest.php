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
        return [
            'deal_id' => 'required|exists:deals,id',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'status' => [
                'nullable',
                Rule::in([
                    Order::STATUS_PENDING,
                    Order::STATUS_PAID,
                    Order::STATUS_FAILED,
                ]),
            ],
            'payment_method' => 'nullable|string|max:50',
            'paid_at' => 'nullable|date',
            'payment_intent_id' => 'nullable|string|max:255',
            'charge_id' => 'nullable|string|max:255',
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
