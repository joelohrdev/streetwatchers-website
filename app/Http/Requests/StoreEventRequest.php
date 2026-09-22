<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * A meetup's details. The date and times are entered in the meetup's own time zone and stored in UTC.
 */
class StoreEventRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:5000'],
            'location_name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date_format:Y-m-d'],
            'starts_at_time' => ['required', 'date_format:H:i'],
            'ends_at_time' => ['required', 'date_format:H:i', 'after:starts_at_time'],
            'timezone' => ['required', 'timezone:all'],
            'rsvps_enabled' => ['required', 'boolean'],
            'rsvp_limit' => ['nullable', 'integer', 'between:1,1000'],
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
            'ends_at_time.after' => 'The meetup must end after it starts.',
            'rsvp_limit.between' => 'The limit must be between 1 and 1,000 people.',
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
                if ($validator->errors()->hasAny(['date', 'starts_at_time', 'timezone'])) {
                    return;
                }

                if ($this->startsAt()->isPast()) {
                    $validator->errors()->add('date', 'The meetup must start in the future.');
                }
            },
        ];
    }

    public function startsAt(): CarbonImmutable
    {
        return $this->moment('starts_at_time');
    }

    public function endsAt(): CarbonImmutable
    {
        return $this->moment('ends_at_time');
    }

    /**
     * The meetup's details ready to save, with times converted to UTC.
     *
     * @return array<string, mixed>
     */
    public function meetupAttributes(): array
    {
        $rsvpsEnabled = $this->boolean('rsvps_enabled');

        return [
            ...$this->safe()->only(['title', 'description', 'location_name', 'timezone']),
            'starts_at' => $this->startsAt()->utc(),
            'ends_at' => $this->endsAt()->utc(),
            'rsvps_enabled' => $rsvpsEnabled,
            'rsvp_limit' => $rsvpsEnabled ? $this->validated('rsvp_limit') : null,
        ];
    }

    private function moment(string $timeField): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat(
            'Y-m-d H:i',
            $this->input('date').' '.$this->input($timeField),
            $this->input('timezone'),
        );
    }
}
