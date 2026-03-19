<?php

namespace App\Traits;

trait HasTestPrefix
{
    /**
     * Prefix a value with "Test" when the model is marked as test.
     *
     * @param  string|null  $value
     * @return string
     */
    protected function prefixTest(?string $value): string
    {
        if (!$value) {
            return '';
        }

        if ($this->is_test && !app()->runningUnitTests() && !str_starts_with($value, 'Test ')) {
            return "Test {$value}";
        }

        return $value;
    }
}