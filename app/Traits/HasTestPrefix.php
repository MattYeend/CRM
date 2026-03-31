<?php

namespace App\Traits;

/**
 * Provides helper functionality for prefixing values when a model is
 * marked as test.
 *
 * Automatically prefixes a given string value with "Test" when the model
 * has the is_test flag enabled, excluding execution during unit tests
 * and avoiding duplicate prefixes.
 */
trait HasTestPrefix
{
    /**
     * Prefix a value with "Test" when the model is marked as test.
     *
     * Returns an empty string if the value is null or empty. If the model
     * is flagged as a test entity, the value is prefixed with "Test "
     * unless already prefixed or running within unit tests.
     *
     * @param  string|null $value The value to conditionally prefix.
     *
     * @return string The prefixed or original value.
     */
    protected function prefixTest(?string $value): string
    {
        if (! $value) {
            return '';
        }

        if (
            $this->is_test &&
            ! app()->runningUnitTests() &&
            ! str_starts_with($value, 'Test ')
        ) {
            return "Test {$value}";
        }

        return $value;
    }
}
