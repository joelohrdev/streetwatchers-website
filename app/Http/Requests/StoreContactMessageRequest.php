<?php

namespace App\Http\Requests;

use App\Enums\ContactTopic;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactMessageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'topic' => ['required', Rule::enum(ContactTopic::class)],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
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
            'topic.required' => 'Choose what your message is about.',
            'message.min' => 'Tell us a little more so we can help.',
        ];
    }

    /**
     * Whether the hidden "website" field was filled in, which only automated spam bots do.
     */
    public function isSpam(): bool
    {
        return $this->filled('website');
    }
}
