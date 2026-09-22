<?php

namespace App\Http\Requests\Settings;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...$this->profileRules($this->user()->id),
            // Instagram usernames are up to 30 letters, numbers, full stops and underscores.
            'instagram_handle' => ['nullable', 'string', 'max:30', 'regex:/^[a-z0-9._]+$/'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'instagram_handle.max' => 'Instagram usernames are 30 characters or fewer.',
            'instagram_handle.regex' => 'Instagram usernames only use letters, numbers, full stops and underscores.',
        ];
    }

    /**
     * Accept the handle however it's pasted, as "@name", "name" or a profile link, and keep only the username.
     * Instagram usernames aren't case-sensitive, so it's stored in lowercase.
     */
    protected function prepareForValidation(): void
    {
        $handle = trim((string) $this->input('instagram_handle'));
        $handle = preg_replace('#^(https?://)?(www\.)?instagram\.com/#i', '', $handle);
        $handle = preg_replace('#[?\#].*$#', '', $handle);
        $handle = strtolower(trim(ltrim($handle, '@'), '/'));

        $this->merge(['instagram_handle' => $handle === '' ? null : $handle]);
    }
}
