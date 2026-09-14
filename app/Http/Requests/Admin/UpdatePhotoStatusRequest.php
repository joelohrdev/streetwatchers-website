<?php

namespace App\Http\Requests\Admin;

use App\Enums\PhotoStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePhotoStatusRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(PhotoStatus::class)->only([PhotoStatus::Flagged, PhotoStatus::Removed])],
            'reason' => ['required', 'string', 'max:2000'],
        ];
    }
}
