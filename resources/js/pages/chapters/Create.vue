<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { LoaderCircle, LocateFixed } from '@lucide/vue';
import { ref } from 'vue';
import { store } from '@/actions/App/Http/Controllers/ChapterController';
import ComboboxField from '@/components/marketing/ComboboxField.vue';
import FormField from '@/components/marketing/FormField.vue';
import { primaryButtonClass, secondaryButtonClass } from '@/lib/marketing';
import { dashboard } from '@/routes';

defineProps<{
    submittedChapter: string | null;
    countries: { value: string; label: string }[];
}>();

const DESCRIPTION_LIMIT = 5000;

const description = ref('');
const latitude = ref('');
const longitude = ref('');

const locating = ref(false);
const locationError = ref<string | null>(null);

/** Fill the coordinates from the browser, rounded to roughly neighbourhood precision. */
function useCurrentLocation(): void {
    if (!('geolocation' in navigator)) {
        locationError.value =
            'Your browser cannot share its location. Enter the coordinates instead.';

        return;
    }

    locating.value = true;
    locationError.value = null;

    navigator.geolocation.getCurrentPosition(
        (position) => {
            latitude.value = position.coords.latitude.toFixed(4);
            longitude.value = position.coords.longitude.toFixed(4);
            locating.value = false;
        },
        () => {
            locationError.value =
                'We could not get your location. Check the browser permission or enter the coordinates instead.';
            locating.value = false;
        },
        { timeout: 10000 },
    );
}

const inputValue = (event: Event): string =>
    (event.target as HTMLInputElement).value;

const sectionLabelClass =
    'font-display text-xs font-semibold tracking-[0.18em] uppercase';

const nextSteps = [
    "You are the group's first admin, so you will be able to run its events and content once it's live.",
    'Every group needs at least two admins before it can be approved. We will get in touch to add a co-organiser.',
    'Our team reviews the group and it appears in the directory once it is approved.',
];
</script>

<template>
    <Head title="Start a group" />

    <section class="mx-auto w-full max-w-2xl px-6 py-20 md:px-10 md:py-28">
        <p
            class="font-display text-ink-soft text-xs font-semibold tracking-[0.18em] uppercase"
        >
            Groups
        </p>

        <template v-if="submittedChapter">
            <h1
                class="font-display mt-4 text-3xl font-extrabold tracking-tight uppercase md:text-4xl"
            >
                {{ submittedChapter }} has been submitted
            </h1>
            <p class="text-ink-soft mt-6 text-lg leading-relaxed">
                Thanks for starting a group. Here is what happens next.
            </p>

            <ol class="border-hairline mt-12 border-t">
                <li
                    v-for="(step, index) in nextSteps"
                    :key="index"
                    class="border-hairline flex gap-6 border-b py-6"
                >
                    <span
                        class="font-display shrink-0 text-sm font-extrabold tabular-nums"
                    >
                        {{ String(index + 1).padStart(2, '0') }}
                    </span>
                    <span class="leading-relaxed">{{ step }}</span>
                </li>
            </ol>

            <div class="mt-12 sm:max-w-xs">
                <Link :href="dashboard()" :class="primaryButtonClass">
                    Back to your memberships
                </Link>
            </div>
        </template>

        <template v-else>
            <h1
                class="font-display mt-4 text-3xl font-extrabold tracking-tight uppercase md:text-5xl"
            >
                Start a group
            </h1>
            <p class="text-ink-soft mt-6 text-lg leading-relaxed">
                Groups are a few local photographers who walk, shoot and edit
                together. Tell us about yours and we will review it.
            </p>

            <Form
                v-bind="store.form()"
                class="mt-14 flex flex-col gap-14"
                v-slot="{ errors, processing }"
            >
                <div
                    class="border-hairline flex flex-col gap-10 border-t pt-10"
                >
                    <div>
                        <h2 :class="sectionLabelClass">About the group</h2>
                        <p class="text-ink-soft mt-3 leading-relaxed">
                            How the group appears in the directory.
                        </p>
                    </div>

                    <FormField
                        id="name"
                        label="Group name"
                        name="name"
                        required
                        maxlength="255"
                        placeholder="Glasgow Streetwatchers"
                        :error="errors.name"
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
                                placeholder="Who the group is for, where you like to shoot and how often you meet."
                                class="text-ink placeholder:text-ink-soft/60 w-full resize-y bg-transparent text-base focus:outline-none"
                            />
                        </div>
                        <div
                            class="mt-2 flex items-start justify-between gap-4"
                        >
                            <p
                                v-if="errors.description"
                                class="text-ink text-sm"
                            >
                                {{ errors.description }}
                            </p>
                            <p
                                class="text-ink-soft ml-auto text-xs tabular-nums"
                            >
                                {{ description.length }} /
                                {{ DESCRIPTION_LIMIT }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="border-hairline flex flex-col gap-10 border-t pt-10"
                >
                    <div>
                        <h2 :class="sectionLabelClass">Location</h2>
                        <p class="text-ink-soft mt-3 leading-relaxed">
                            Where the group is based. The coordinates place it
                            on the group map.
                        </p>
                    </div>

                    <div class="grid gap-10 sm:grid-cols-2">
                        <FormField
                            id="city"
                            label="City"
                            name="city"
                            required
                            autocomplete="address-level2"
                            placeholder="Glasgow"
                            :error="errors.city"
                        />
                        <ComboboxField
                            id="country"
                            label="Country"
                            name="country"
                            required
                            placeholder="Search for a country"
                            :options="countries"
                            :error="errors.country"
                        />
                    </div>

                    <div class="grid gap-10 sm:grid-cols-2">
                        <FormField
                            id="latitude"
                            label="Latitude"
                            type="number"
                            name="latitude"
                            step="any"
                            min="-90"
                            max="90"
                            required
                            placeholder="55.8642"
                            :value="latitude"
                            :error="errors.latitude"
                            @input="latitude = inputValue($event)"
                        />
                        <FormField
                            id="longitude"
                            label="Longitude"
                            type="number"
                            name="longitude"
                            step="any"
                            min="-180"
                            max="180"
                            required
                            placeholder="-4.2518"
                            :value="longitude"
                            :error="errors.longitude"
                            @input="longitude = inputValue($event)"
                        />
                    </div>

                    <div class="flex flex-col gap-3">
                        <div class="sm:max-w-xs">
                            <button
                                type="button"
                                :disabled="locating"
                                :class="secondaryButtonClass"
                                @click="useCurrentLocation"
                            >
                                <LoaderCircle
                                    v-if="locating"
                                    class="size-4 animate-spin"
                                />
                                <LocateFixed v-else class="size-4" />
                                Use my current location
                            </button>
                        </div>
                        <p v-if="locationError" class="text-ink text-sm">
                            {{ locationError }}
                        </p>
                    </div>
                </div>

                <div
                    class="border-hairline flex flex-col gap-4 border-t pt-10 sm:flex-row sm:items-center"
                >
                    <button
                        type="submit"
                        :disabled="processing"
                        :class="[primaryButtonClass, 'sm:w-auto']"
                    >
                        <LoaderCircle
                            v-if="processing"
                            class="size-4 animate-spin"
                        />
                        Submit for approval
                    </button>
                </div>
            </Form>
        </template>
    </section>
</template>
