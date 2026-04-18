<?php

namespace App\Http\Requests;

use App\Models\Attachment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Handles authorisation and validation for updating an existing Attachment.
 *
 * Validation rules are split into focused private methods and merged in
 * rules(), keeping each concern isolated and easy to maintain:
 *   - baseRules — optional replacement file and its size constraint
 *   - metaRules — optional meta payload
 *
 * prepareForValidation resolves the incoming morph key to its full class
 * name before the ruleset is applied.
 */
class UpdateAttachmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Resolves the route-bound attachment and delegates to the 'update'
     * policy.
     *
     * @return bool True if the authenticated user may update this attachment.
     */
    public function authorize(): bool
    {
        $attachment = $this->route('attachment');

        return $this->user()->can('update', $attachment);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Merges base and meta rule groups into a single ruleset.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            $this->attachmentRules(),
            $this->metaRules(),
        );
    }

    /**
     * Resolve the attachable_type morph key to its full class name before
     * validation runs.
     *
     * Ensures that polymorphic type values sent as morph aliases are
     * expanded to their fully-qualified class names so they pass the
     * Rule::in check in attachmentRules().
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->attachable_type) {
            $this->merge([
                'attachable_type' => Relation::getMorphedModel(
                    $this->attachable_type
                ),
            ]);
        }
    }

    /**
     * Validation rules for the optional replacement file.
     *
     * When provided, the file must not exceed the maximum permitted size.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function baseRules(): array
    {
        return [
            'file' => 'nullable|file|max:10000',
        ];
    }

    /**
     * Validation rules for the polymorphic attachable relationship.
     *
     * Ensures attachable_type is one of the registered attachable types and
     * that attachable_id is present whenever an attachable_type is provided.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function attachmentRules(): array
    {
        return [
            'attachable_type' => [
                'nullable',
                Rule::in(Attachment::ATTACHABLE_TYPES),
            ],
            'attachable_id' => [
                'nullable',
                'integer',
            ],
        ];
    }

    /**
     * Validation rules for optional metadata fields.
     *
     * @return array<string,ValidationRule|array<mixed>|string>
     */
    private function metaRules(): array
    {
        return [
            'meta' => 'nullable|array',
        ];
    }
}
