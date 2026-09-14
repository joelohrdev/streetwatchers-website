<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCorrespondentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists(User::class, 'id')],
            'bio' => ['required', 'string', 'max:5000'],
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
                if ($validator->errors()->has('user_id')) {
                    return;
                }

                if ($this->correspondentUser()->correspondent?->is_active) {
                    $validator->errors()->add('user_id', 'This user is already an active correspondent.');
                }
            },
        ];
    }

    /**
     * The user being granted correspondent status.
     */
    public function correspondentUser(): User
    {
        return User::query()->with('correspondent')->findOrFail($this->integer('user_id'));
    }
}
