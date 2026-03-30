<?php

namespace App\Http\Requests;

use App\Rules\HasCorrectAnswer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Handles authorisation and validation for updating an existing Learning.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — the learning title
 *   - questionAndAnswerRules — nested question and answer structure,
 *      including the HasCorrectAnswer custom rule
 *   - metaRules — optional description, completion flag, and meta payload
 */
class UpdateLearningRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound learning and delegates to the 'update' policy.
     *
     * @return bool True if the authenticated user may update this learning.
     */
    public function authorize(): bool
    {
        $learning = $this->route('learning');

        return $this->user()->can('update', $learning);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Merges base, question and answer, and meta rule groups into a single
     * ruleset.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            $this->questionAndAnswerRules(),
            $this->metaRules(),
        );
    }

    /**
     * Validation rules for the core learning title field.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
        ];
    }

    /**
     * Validation rules for the nested question and answer structure.
     *
     * Each question may carry multiple answers, and the HasCorrectAnswer
     * custom rule enforces that at least one answer is marked correct per
     * question.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function questionAndAnswerRules(): array
    {
        return [
            'questions' => 'nullable|array',
            'questions.*.question' => 'nullable|string',
            'questions.*.answers' => ['array', new HasCorrectAnswer()],
            'questions.*.answers.*.answer' => 'nullable|string',
            'questions.*.answers.*.is_correct' => 'boolean',
        ];
    }

    /**
     * Validation rules for optional descriptive, completion, and metadata
     * fields.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function metaRules(): array
    {
        return [
            'description' => 'nullable|string',
            'is_complete' => 'boolean',
            'meta' => 'nullable|array',
        ];
    }
}
