<?php

namespace App\Http\Requests;

use App\Enums\ChapterMemberRole;
use App\Enums\ChapterStatus;
use App\Models\Chapter;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreChapterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(Chapter::class, 'name')],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'description' => ['required', 'string', 'max:5000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
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
            'name.unique' => 'A group with this name already exists.',
            'cover_image.max' => 'The cover image must be 5 MB or smaller.',
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
                $hasPendingProposal = $this->user()
                    ->chapters()
                    ->where('status', ChapterStatus::Pending)
                    ->wherePivot('role', ChapterMemberRole::Admin)
                    ->exists();

                if ($hasPendingProposal) {
                    $validator->errors()->add('name', 'You already have a group waiting for approval.');
                }
            },
        ];
    }
}
