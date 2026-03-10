<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Task::class);
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
            $this->taskableRules(),
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
            'description' => 'nullable|string',
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * Taskable rules
     *
     * @return array
     */
    private function taskableRules(): array
    {
        return [
            'taskable_type' => [
                'required',
                Rule::in(['deal', 'contact', 'company', 'task', 'user']),
            ],
            'taskable_id' => ['required', 'integer'],
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
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'status' => [
                'nullable',
                Rule::in(['pending', 'completed', 'canceled']),
            ],
            'due_at' => 'nullable|date',
        ];
    }
}
