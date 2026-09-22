<?php

namespace App\Http\Requests;

use App\Enums\ChapterMemberRole;
use App\Enums\ChapterStatus;
use App\Enums\Country;
use App\Models\Chapter;
use App\Models\Setting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
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
            'country' => ['required', Rule::enum(Country::class)],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'description' => ['required', 'string', 'max:5000'],
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
            fn (Validator $validator) => $this->checkGroupSpacing($validator),
        ];
    }

    /**
     * Groups must be a set distance apart, from the platform settings. $except is a group being moved, which
     * isn't measured against itself.
     */
    protected function checkGroupSpacing(Validator $validator, ?Chapter $except = null): void
    {
        if ($validator->errors()->hasAny(['latitude', 'longitude'])) {
            return;
        }

        $radius = Setting::groupRadiusInMiles();
        $nearest = Chapter::nearestWithinMiles($this->float('latitude'), $this->float('longitude'), $radius, $except);

        if ($nearest === null) {
            return;
        }

        $miles = max(1, (int) round($nearest->distanceInMilesTo(new Chapter($this->only(['latitude', 'longitude'])))));
        $distance = $miles.' '.Str::plural('mile', $miles);

        $validator->errors()->add('latitude', match (true) {
            $nearest->status === ChapterStatus::Pending => "A group is already waiting for approval {$distance} from here. Groups must be at least {$radius} miles apart.",
            $except !== null => "{$nearest->name} is {$distance} from here. Groups must be at least {$radius} miles apart.",
            default => "{$nearest->name} is {$distance} from here. Groups must be at least {$radius} miles apart, so join that group instead.",
        });
    }
}
