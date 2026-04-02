<?php

namespace App\Http\Requests;

use App\Models\Learning;
use App\Rules\HasCorrectAnswer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Handles authorisation and validation for storing a new Learning.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — the learning title
 *   - questionAndAnswerRules — nested question and answer structure,
 *      including the HasCorrectAnswer custom rule
 *   - metaRules — optional description and meta payload
 */
class StoreLearningRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to the 'create' policy for the Learning model.
     *
     * @return bool True if the authenticated user may create learnings.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Learning::class);
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
            'title' => 'required|string|max:255',
            'pass_score' => 'nullable|integer|min:0|max:100',
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
     * Validation rules for optional descriptive and metadata fields.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function metaRules(): array
    {
        return [
            'description' => 'nullable|string',
            'meta' => 'nullable|array',
        ];
    }
}
