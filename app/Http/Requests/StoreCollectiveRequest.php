<?php

namespace App\Http\Requests;

use App\Models\Collective;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StoreCollectiveRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', $this->uniqueNameRule()],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'based_in' => ['nullable', 'string', 'max:255'],
            'website_url' => ['nullable', 'url:http,https', 'max:255'],
            'instagram_url' => ['nullable', 'url:http,https', 'max:255', 'regex:/^https?:\/\/(www\.)?instagram\.com\//i'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_open_for_applications' => ['required', 'boolean'],
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
            'name.unique' => 'A collective with this name already exists.',
            'description.min' => 'Tell people a little more about what the collective does.',
            'instagram_url.regex' => 'Use a link to an Instagram profile, such as https://instagram.com/yourcollective.',
            'logo.max' => 'The logo must be 2 MB or smaller.',
        ];
    }

    /**
     * Names must be unique among collectives still on the platform.
     */
    protected function uniqueNameRule(): Unique
    {
        return Rule::unique(Collective::class, 'name')->withoutTrashed();
    }
}
