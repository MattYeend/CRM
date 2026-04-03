<?php

namespace App\Traits\Learning;

trait HasValidationStatus
{
    /**
     * Get the validation metadata for the model.
     * This method returns an associative array containing metadata about the validation status of the model, which can be used in the UI to provide visual cues about whether the model is valid or invalid. The metadata includes:
     * - 'status': A string indicating the validation status ('valid' or 'invalid').
     * - 'label': A human-readable label for the validation status (e.g., 'Valid' or 'Invalid').
     * - 'color': A color name (e.g., 'green' for valid, 'red' for invalid) that can be used in the UI to visually indicate the validation status.
     * - 'icon': An icon name (e.g., 'check' for valid, 'x' for invalid) that can be used in the UI to visually represent the validation status.
     * - 'badge': A CSS class name (e.g., 'badge-success' for valid, 'badge-danger' for invalid) that can be applied to a badge element in the UI to visually indicate the validation status.
     * - 'text_class': A CSS class name (e.g., 'text-success' for valid, 'text-danger' for invalid) that can be applied to text elements in the UI to visually indicate the validation status.
     * The method uses the isValid() method to determine the validation status of the model and returns the appropriate metadata based on whether the model is valid or invalid.
     * Note: The isValid() method must be implemented in the model that uses this trait to determine the validation status of the model.
     * @see isValid() This method relies on the isValid() method to determine the validation status of the model. The isValid() method should return a boolean indicating whether the model is valid or not.
     * 
     * @return array Returns an associative array containing metadata about the validation status of the model, including:
     * - 'status': A string indicating the validation status ('valid' or 'invalid').
     * - 'label': A human-readable label for the validation status (e.g., 'Valid' or 'Invalid').
     * - 'color': A color name (e.g., 'green' for valid, 'red' for invalid) that can be used in the UI to visually indicate the validation status.
     * - 'icon': An icon name (e.g., 'check' for valid, 'x' for invalid) that can be used in the UI to visually represent the validation status.
     * - 'badge': A CSS class name (e.g., 'badge-success' for valid, 'badge-danger' for invalid) that can be applied to a badge element in the UI to visually indicate the validation status.
     * - 'text_class': A CSS class name (e.g., 'text-success' for valid, 'text-danger' for invalid) that can be applied to text elements in the UI to visually indicate the validation status.
     */
    protected function validationMeta(): array
    {
        return $this->isValid()
            ? [
                'status' => 'valid',
                'label' => 'Valid',
                'color' => 'green',
                'icon' => 'check',
                'badge' => 'badge-success',
                'text_class' => 'text-success',
            ]
            : [
                'status' => 'invalid',
                'label' => 'Invalid',
                'color' => 'red',
                'icon' => 'x',
                'badge' => 'badge-danger',
                'text_class' => 'text-danger',
            ];
    }

    /**
     * Get the validation status of the model.
     *
     * @return string Returns 'valid' if the model is valid, otherwise 'invalid'.
     */
    public function getValidationStatusAttribute(): string
    {
        return $this->validationMeta()['status'];
    }

    /**
     * Get the validation status label for the model.
     *
     * @return string Returns a human-readable label for the validation status, such as 'Valid' or 'Invalid'.
     */
    public function getValidationStatusLabelAttribute(): string
    {
        return $this->validationMeta()['label'];
    }

    /**
     * Get the color associated with the validation status.
     *
     * @return string Returns a color name (e.g., 'green' for valid, 'red' for invalid) that can be used in the UI to visually indicate the validation status.
     */
    public function getValidationStatusColorAttribute(): string
    {
        return $this->validationMeta()['color'];
    }

    /**
     * Get the icon name associated with the validation status.
     *
     * @return string Returns an icon name (e.g., 'check' for valid, 'x' for invalid) that can be used in the UI to visually represent the validation status.
     */
    public function getValidationStatusIconAttribute(): string
    {
        return $this->validationMeta()['icon'];
    }

    /**
     * Get the CSS class for the badge representing the validation status.
     *
     * @return string Returns a CSS class name (e.g., 'badge-success' for valid, 'badge-danger' for invalid) that can be applied to a badge element in the UI to visually indicate the validation status.
     */
    public function getValidationStatusBadgeAttribute(): string
    {
        return $this->validationMeta()['badge'];
    }

    /**
     * Get the CSS class for text representing the validation status.
     *
     * @return string Returns a CSS class name (e.g., 'text-success' for valid, 'text-danger' for invalid) that can be applied to text elements in the UI to visually indicate the validation status.
     */
    public function getValidationStatusClassAttribute(): string
    {
        return $this->validationMeta()['text_class'];
    }
}
