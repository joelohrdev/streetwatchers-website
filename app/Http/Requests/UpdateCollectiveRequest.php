<?php

namespace App\Http\Requests;

use App\Models\Collective;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Unique;

class UpdateCollectiveRequest extends StoreCollectiveRequest
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
            'remove_logo' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * A collective may keep its own name when it is edited.
     */
    protected function uniqueNameRule(): Unique
    {
        $collective = $this->route('collective');

        return parent::uniqueNameRule()->ignore($collective instanceof Collective ? $collective->id : null);
    }
}
