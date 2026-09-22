<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { LoaderCircle, LocateFixed } from '@lucide/vue';
import { ref } from 'vue';
import ComboboxField from '@/components/marketing/ComboboxField.vue';
import FormField from '@/components/marketing/FormField.vue';
import { primaryButtonClass, secondaryButtonClass } from '@/lib/marketing';

export type GroupDetails = {
    name: string;
    description: string;
    city: string;
    country: string;
    latitude: number;
    longitude: number;
};

/**
 * The fields for proposing a group or editing one: name, description and location.
 */
const { action, group, countries, submitLabel } = defineProps<{
    action: { action: string; method: 'get' | 'post' };
    group?: GroupDetails;
    countries: { value: string; label: string }[];
    submitLabel: string;
}>();

const DESCRIPTION_LIMIT = 5000;

const description = ref(group?.description ?? '');
const latitude = ref(group ? String(group.latitude) : '');
const longitude = ref(group ? String(group.longitude) : '');

const locating = ref(false);
const locationError = ref<string | null>(null);

/**
 * Fill the coordinates from the browser, rounded to two decimal places (about a kilometer). They become the
 * group's public map pin, so this keeps a proposer's exact position, such as their home, off the map.
 */
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
            latitude.value = position.coords.latitude.toFixed(2);
            longitude.value = position.coords.longitude.toFixed(2);
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
</script>

<template>
    <Form
        v-bind="action"
        class="mt-14 flex flex-col gap-14"
        v-slot="{ errors, processing }"
    >
        <div class="border-hairline flex flex-col gap-10 border-t pt-10">
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
                :value="group?.name"
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
                <div class="mt-2 flex items-start justify-between gap-4">
                    <p v-if="errors.description" class="text-ink text-sm">
                        {{ errors.description }}
                    </p>
                    <p class="text-ink-soft ml-auto text-xs tabular-nums">
                        {{ description.length }} / {{ DESCRIPTION_LIMIT }}
                    </p>
                </div>
            </div>
        </div>

        <div class="border-hairline flex flex-col gap-10 border-t pt-10">
            <div>
                <h2 :class="sectionLabelClass">Location</h2>
                <p class="text-ink-soft mt-3 leading-relaxed">
                    Where the group is based. The coordinates place it on the
                    group map.
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
                    :value="group?.city"
                    :error="errors.city"
                />
                <ComboboxField
                    id="country"
                    label="Country"
                    name="country"
                    required
                    placeholder="Search for a country"
                    :options="countries"
                    :value="group?.country"
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
                <LoaderCircle v-if="processing" class="size-4 animate-spin" />
                {{ submitLabel }}
            </button>
        </div>
    </Form>
</template>
