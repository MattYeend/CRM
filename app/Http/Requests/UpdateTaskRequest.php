<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for updating an existing Task.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — title, description, and optional assignee
 *   - metaRules — priority, status, due date, and optional meta payload
 */
class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound task and delegates to the 'update' policy.
     *
     * @return bool True if the authenticated user may update this task.
     */
    public function authorize(): bool
    {
        $task = $this->route('task');

        return $this->user()->can('update', $task);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Merges base, taskable and meta rule groups into a single ruleset.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
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
     * Resolve the taskable_type morph key to its full class name before
     * validation runs.
     *
     * Ensures that polymorphic type values sent as morph aliases are
     * expanded to their fully-qualified class names so they pass the
     * Rule::in check in noteRules().
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->taskable_type) {
            $this->merge([
                'taskable_type' => Relation::getMorphedModel(
                    $this->taskable_type
                ),
            ]);
        }
    }

    /**
     * Validation rules for core task fields.
     *
     * Title is optional for updates; description is optional, and assigned_to
     * must reference an existing user when provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ],
        ];
    }

    /**
     * Validation rules for the polymorphic notable relationship.
     *
     * Ensures notable_type is one of the registered notable types and
     * that notable_id is present whenever a notable_type is provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function taskableRules(): array
    {
        return [
            'taskable_type' => [
                'nullable',
                Rule::in(Task::TASKABLE_TYPES),
            ],
            'taskable_id' => [
                'nullable',
                'integer',
                'required_with:taskable_type',
            ],
        ];
    }

    /**
     * Validation rules for task priority, status, due date, and metadata.
     *
     * Priority and status are each constrained to the allowed values
     * defined on the Task model.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function metaRules(): array
    {
        return [
            'priority' => [
                'nullable',
                Rule::in([
                    Task::PRIORITY_LOW,
                    Task::PRIORITY_MEDIUM,
                    Task::PRIORITY_HIGH,
                ]),
            ],
            'status' => [
                'nullable',
                Rule::in([
                    Task::STATUS_PENDING,
                    Task::STATUS_COMPLETED,
                    Task::STATUS_CANCELLED,
                ]),
            ],
            'due_at' => 'nullable|date',
            'meta' => 'nullable|array',
        ];
    }
}
