<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Ensures that a set of answers contains at least one correct option.
 *
 * This validation rule expects an array of answer data, where each item
 * includes an `is_correct` flag. Validation fails if none of the provided
 * answers are marked as correct.
 */
class HasCorrectAnswer implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * Validates that the given value contains at least one item with
     * `is_correct` set to true.
     *
     * @param  string   $attribute The name of the attribute being validated
     * @param  mixed    $value The value of the attribute (expected array)
     * @param  Closure  $fail Callback to invoke on validation failure
     *
     * @return void
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
