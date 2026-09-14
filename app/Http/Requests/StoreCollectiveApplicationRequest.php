<?php

namespace App\Http\Requests;

use App\Models\Collective;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCollectiveApplicationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:20', 'max:2000'],
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
            'message.required' => 'Tell the founders a little about yourself.',
            'message.min' => 'Tell the founders a little more about yourself.',
        ];
    }

    /**
     * Get the "after" validation callables for the request.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $collective = $this->route('collective');

                if (! $collective instanceof Collective) {
                    return;
                }

                $problem = match (true) {
                    ! $collective->is_open_for_applications => 'This collective is not taking applications right now.',
                    $collective->roleOf($this->user()) !== null => 'You are already in this collective.',
                    $collective->hasPendingApplicationFrom($this->user()) => 'You have already applied. The founders will get back to you.',
                    default => null,
                };

                if ($problem !== null) {
                    $validator->errors()->add('message', $problem);
                }
            },
        ];
    }
}
