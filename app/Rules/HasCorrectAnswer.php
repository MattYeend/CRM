<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HasCorrectAnswer implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=):
     * \Illuminate\Translation\PotentiallyTranslatedString
     * $fail
     */
    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
    ): void {
        if (! collect($value)->contains('is_correct', true)) {
            $fail('Each question must have at least one correct answer.');
        }
    }
}
