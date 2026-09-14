<?php

namespace App\Http\Requests\Admin;

use App\Enums\CritiqueGroupStatus;
use App\Models\CritiqueGroup;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class MoveCritiqueGroupMemberRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'target_group_id' => ['required', 'integer', Rule::exists(CritiqueGroup::class, 'id')],
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
                if ($validator->errors()->has('target_group_id')) {
                    return;
                }

                $target = $this->targetGroup();
                $user = $this->route('user');

                if (! $user instanceof User) {
                    return;
                }

                if ($target->is($this->route('critiqueGroup'))) {
                    $validator->errors()->add('target_group_id', 'Choose a different group.');
                } elseif ($target->status === CritiqueGroupStatus::Closed) {
                    $validator->errors()->add('target_group_id', 'That group is closed.');
                } elseif ($target->members()->whereKey($user->id)->exists()) {
                    $validator->errors()->add('target_group_id', 'This user is already in that group.');
                } elseif ($target->members()->count() >= $target->max_members) {
                    $validator->errors()->add('target_group_id', 'That group is full.');
                }
            },
        ];
    }

    /**
     * The group the member is moving into.
     */
    public function targetGroup(): CritiqueGroup
    {
        return CritiqueGroup::query()->findOrFail($this->integer('target_group_id'));
    }
}
