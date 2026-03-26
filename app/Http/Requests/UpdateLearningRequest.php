<?php

namespace App\Http\Requests;

use App\Rules\HasCorrectAnswer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLearningRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $learning = $this->route('learning');

        return $this->user()->can('update', $learning);
    }

    /**
     * Get the validation rules that apply to the request.
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
     * Base rules
     *
     * @return array
     */
    private function baseRules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
        ];
    }

    /**
     * Question and answer rules
     *
     * @return array
     */
    private function questionAndAnswerRules(): array
    {
        return [
            'questions' => ['nullable', 'array'],
            'questions.*.question' => ['nullable', 'string'],
            'questions.*.answers' => ['array', new HasCorrectAnswer()],
            'questions.*.answers.*.answer' => ['nullable', 'string'],
            'questions.*.answers.*.is_correct' => ['boolean'],
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
            'description' => 'nullable|string',
            'is_complete' => 'boolean',
            'meta' => 'nullable|array',
        ];
    }
}
