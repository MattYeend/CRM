<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $task = $this->route('task');

        return $this->user()->can('update', $task);
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
            'description' => 'nullable|string',
            'assigned_to' => [
                'nullable',
                'integer',
                'exists:users,id',
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
            'priority' => [
                'nullable',
                Rule::in(['low', 'medium', 'high']),
            ],
            'status' => [
                'nullable',
                Rule::in([
                    Task::STATUS_PENDING,
                    Task::STATUS_COMPLETED,
                    Task::STATUS_CANCELLED,
                ])
            ],
            'due_at' => 'nullable|date',
            'meta' => 'nullable|array',
        ];
    }
}
