<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'new_photos_require_review' => ['required', 'boolean'],
            'announcement_banner' => ['nullable', 'string', 'max:500'],
            'group_radius_miles' => ['required', 'integer', 'between:1,250'],
        ];
    }
}
