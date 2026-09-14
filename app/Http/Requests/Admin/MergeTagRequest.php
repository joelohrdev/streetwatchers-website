<?php

namespace App\Http\Requests\Admin;

use App\Models\Tag;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MergeTagRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'target_tag_id' => [
                'required',
                'integer',
                Rule::exists(Tag::class, 'id'),
                Rule::notIn([$this->sourceTag()->id]),
            ],
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
            'target_tag_id.not_in' => 'A tag cannot be merged into itself.',
        ];
    }

    /**
     * The tag being merged away, bound from the route.
     */
    public function sourceTag(): Tag
    {
        $tag = $this->route('tag');

        abort_unless($tag instanceof Tag, 404);

        return $tag;
    }

    /**
     * The tag that will receive the merged photos.
     */
    public function targetTag(): Tag
    {
        return Tag::query()->findOrFail($this->integer('target_tag_id'));
    }
}
