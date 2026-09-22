<?php

namespace App\Http\Requests;

use App\Models\Chapter;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * An organizer editing their group. The same details as proposing one, except the group keeps its own name and
 * isn't measured against itself for spacing.
 */
class UpdateChapterRequest extends StoreChapterRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'name' => ['required', 'string', 'max:255', Rule::unique(Chapter::class, 'name')->ignore($this->chapter())],
        ];
    }

    /**
     * Get the "after" validation callables for the request.
     *
     * Spacing is only checked when the location moves, so a group that was already close to another (for example
     * after the radius setting went up) can still update its name and description.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->locationChanged()) {
                    $this->checkGroupSpacing($validator, $this->chapter());
                }
            },
        ];
    }

    private function locationChanged(): bool
    {
        return $this->float('latitude') !== $this->chapter()->latitude
            || $this->float('longitude') !== $this->chapter()->longitude;
    }

    private function chapter(): Chapter
    {
        return $this->route('chapter');
    }
}
