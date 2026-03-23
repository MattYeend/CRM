<?php

namespace App\Http\Requests;

use App\Models\Learning;
use App\Rules\HasCorrectAnswer;
use Illuminate\Foundation\Http\FormRequest;

class StoreLearningRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Learning::class);
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
            'title' => 'required|string|max:255',
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
            'meta' => 'nullable|array',
        ];
    }
}
