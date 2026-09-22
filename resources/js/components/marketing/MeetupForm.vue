<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { LoaderCircle } from '@lucide/vue';
import { ref } from 'vue';
import FormField from '@/components/marketing/FormField.vue';
import { primaryButtonClass } from '@/lib/marketing';

export type MeetupDetails = {
    title: string;
    description: string;
    location_name: string;
    date: string;
    starts_at_time: string;
    ends_at_time: string;
    timezone: string;
    rsvps_enabled: boolean;
    rsvp_limit: number | null;
};

/**
 * The fields for planning or editing a meetup. Times are entered in the meetup's time zone: the organiser's
 * own zone for a new meetup, or the zone it was planned in when editing.
 */
const { action, meetup, submitLabel } = defineProps<{
    action: { action: string; method: 'get' | 'post' };
    meetup?: MeetupDetails;
    submitLabel: string;
}>();

const DESCRIPTION_LIMIT = 5000;

const description = ref(meetup?.description ?? '');
const rsvpsEnabled = ref(meetup?.rsvps_enabled ?? false);
const timezone =
    meetup?.timezone ?? Intl.DateTimeFormat().resolvedOptions().timeZone;

const sectionLabelClass =
    'font-display text-xs font-semibold tracking-[0.18em] uppercase';
</script>

<template>
    <Form
        v-bind="action"
        class="mt-14 flex flex-col gap-14"
        v-slot="{ errors, processing }"
    >
        <div class="border-hairline flex flex-col gap-10 border-t pt-10">
            <div>
                <h2 :class="sectionLabelClass">The meetup</h2>
            </div>

            <FormField
                id="title"
                label="Title"
                name="title"
                required
                maxlength="120"
                placeholder="Sunday morning walk along the Clyde"
                :value="meetup?.title"
                :error="errors.title"
            />

            <div>
                <label
                    for="description"
                    class="text-ink-soft text-xs tracking-[0.14em] uppercase"
                >
                    Description
                </label>
                <div
                    class="border-hairline focus-within:border-ink mt-3 border-b pb-2 transition-colors"
                >
                    <textarea
                        id="description"
                        v-model="description"
                        name="description"
                        rows="5"
                        required
                        :maxlength="DESCRIPTION_LIMIT"
                        placeholder="The route, what to bring and where to find the group when you arrive."
                        class="text-ink placeholder:text-ink-soft/60 w-full resize-y bg-transparent text-base focus:outline-none"
                    />
                </div>
                <div class="mt-2 flex items-start justify-between gap-4">
                    <p v-if="errors.description" class="text-ink text-sm">
                        {{ errors.description }}
                    </p>
                    <p class="text-ink-soft ml-auto text-xs tabular-nums">
                        {{ description.length }} / {{ DESCRIPTION_LIMIT }}
                    </p>
                </div>
            </div>

            <FormField
                id="location_name"
                label="Meeting point"
                name="location_name"
                required
                maxlength="255"
                placeholder="Outside the Riverside Museum entrance"
                :value="meetup?.location_name"
                :error="errors.location_name"
            />
        </div>

        <div class="border-hairline flex flex-col gap-10 border-t pt-10">
            <div>
                <h2 :class="sectionLabelClass">When</h2>
                <p class="text-ink-soft mt-3 leading-relaxed">
                    Times are in {{ timezone.replaceAll('_', ' ') }}.
                </p>
            </div>

            <input type="hidden" name="timezone" :value="timezone" />

            <FormField
                id="date"
                label="Date"
                type="date"
                name="date"
                required
                :value="meetup?.date"
                :error="errors.date"
            />

            <div class="grid gap-10 sm:grid-cols-2">
                <FormField
                    id="starts_at_time"
                    label="Starts"
                    type="time"
                    name="starts_at_time"
                    required
                    :value="meetup?.starts_at_time"
                    :error="errors.starts_at_time"
                />
                <FormField
                    id="ends_at_time"
                    label="Ends"
                    type="time"
                    name="ends_at_time"
                    required
                    :value="meetup?.ends_at_time"
                    :error="errors.ends_at_time"
                />
            </div>
        </div>

        <div class="border-hairline flex flex-col gap-10 border-t pt-10">
            <div>
                <h2 :class="sectionLabelClass">RSVPs</h2>
                <p class="text-ink-soft mt-3 leading-relaxed">
                    Ask people to RSVP when you need a head count or have
                    limited places. Anyone who RSVPs also joins the group.
                </p>
            </div>

            <!-- The checkbox only submits when ticked, so this always sends true or false. -->
            <input
                type="hidden"
                name="rsvps_enabled"
                :value="rsvpsEnabled ? '1' : '0'"
            />
            <label class="flex cursor-pointer items-center gap-3">
                <input
                    v-model="rsvpsEnabled"
                    type="checkbox"
                    class="accent-ink size-4"
                />
                <span>Take RSVPs for this meetup</span>
            </label>

            <FormField
                v-if="rsvpsEnabled"
                id="rsvp_limit"
                label="Limit (optional)"
                type="number"
                name="rsvp_limit"
                min="1"
                max="1000"
                step="1"
                placeholder="No limit"
                :value="meetup?.rsvp_limit ?? undefined"
                :error="errors.rsvp_limit"
            />
        </div>

        <div class="border-hairline border-t pt-10">
            <button
                type="submit"
                :disabled="processing"
                :class="[primaryButtonClass, 'sm:w-auto']"
            >
                <LoaderCircle v-if="processing" class="size-4 animate-spin" />
                {{ submitLabel }}
            </button>
        </div>
    </Form>
</template>
