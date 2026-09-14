<?php

namespace App\Http\Requests;

use App\Models\Photo;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class StorePhotoRemovalRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'photo_reference' => ['required', 'string', 'max:2048'],
            'reason' => ['required', 'string', 'max:5000'],
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
                if ($validator->errors()->has('photo_reference')) {
                    return;
                }

                if ($this->photo() === null) {
                    $validator->errors()->add('photo_reference', 'We could not find a photo matching that ID or link.');
                }
            },
        ];
    }

    /**
     * Resolve the photo from either a numeric ID or a link ending in the photo's ID, such as /photos/42.
     */
    public function photo(): ?Photo
    {
        return once(function (): ?Photo {
            $reference = trim($this->string('photo_reference'));
            $path = parse_url($reference, PHP_URL_PATH) ?: $reference;
            $id = Str::of($path)->rtrim('/')->afterLast('/')->toString();

            return ctype_digit($id) ? Photo::query()->find((int) $id) : null;
        });
    }
}
