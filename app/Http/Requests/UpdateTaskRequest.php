<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'title' => 'required|string',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|integer|exists:users,id',
            'created_by' => 'nullable|integer|exists:users,id',
            'taskable_type' => 'nullable
                |in:App\Models\Contact,App\Models\Deal,App\Models\Company',
            'taskable_id' => 'nullable|integer',
            'priority' => 'nullable|in:low,medium,high',
            'status' => 'nullable|in:pending,completed,canceled',
            'due_at' => 'nullable|date',
        ];
    }
}
