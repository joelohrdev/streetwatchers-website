<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, LocateFixed, LoaderCircle, Search } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import ChapterMap from '@/components/marketing/ChapterMap.vue';
import type { Coordinates } from '@/lib/geo';
import { distanceInKilometers, formatDistance } from '@/lib/geo';
import { create, show } from '@/routes/chapters';

type Chapter = {
    id: number;
    name: string;
    slug: string;
    city: string;
    country: string;
    latitude: number;
    longitude: number;
    description: string;
    members_count: number;
};

type ChapterResult = Chapter & { distance: number | null };

const props = defineProps<{
    chapters: Chapter[];
    search: string;
}>();

const PAGE_SIZE = 24;

const query = ref(props.search);
const visibleCount = ref(PAGE_SIZE);

const position = ref<Coordinates | null>(null);
const locating = ref(false);
const locationError = ref<string | null>(null);

/** Ask the browser for the visitor's position. It stays on this device and is never sent to the server. */
function findNearest(): void {
    if (!('geolocation' in navigator)) {
        locationError.value =
            'Your browser cannot share its location. Search by city or country instead.';

        return;
    }

    locating.value = true;
    locationError.value = null;

    navigator.geolocation.getCurrentPosition(
        ({ coords }) => {
            position.value = {
                latitude: coords.latitude,
                longitude: coords.longitude,
            };
            query.value = '';
            locating.value = false;
        },
        (error) => {
            locationError.value =
                error.code === error.PERMISSION_DENIED
                    ? 'Location access is turned off for this site. Search by city or country instead.'
                    : 'We could not work out your location. Search by city or country instead.';
            locating.value = false;
        },
        { timeout: 10000, maximumAge: 5 * 60 * 1000 },
    );
}

/** Lowercase and strip accents so "sao paulo" finds "São Paulo". */
const normalize = (value: string): string =>
    value
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '')
        .toLowerCase();

const results = computed<ChapterResult[]>(() => {
    const term = normalize(query.value.trim());

    const matches = props.chapters
        .filter(
            (chapter) =>
                term === '' ||
                [chapter.name, chapter.city, chapter.country].some((field) =>
                    normalize(field).includes(term),
                ),
        )
        .map((chapter) => ({
            ...chapter,
            distance: position.value
                ? distanceInKilometers(position.value, chapter)
                : null,
        }));

    return position.value
        ? matches.sort((a, b) => (a.distance ?? 0) - (b.distance ?? 0))
        : matches;
});

const closest = computed(() =>
    position.value && results.value.length > 0 ? results.value[0] : null,
);

const listed = computed(() =>
    (closest.value ? results.value.slice(1) : results.value).slice(
        0,
        visibleCount.value,
    ),
);

const remaining = computed(
    () => results.value.length - (closest.value ? 1 : 0) - listed.value.length,
);

const countryCount = computed(
    () => new Set(props.chapters.map((chapter) => chapter.country)).size,
);

const hasGroups = computed(() => props.chapters.length > 0);

const plural = (count: number, singular: string, pluralForm: string): string =>
    `${count} ${count === 1 ? singular : pluralForm}`;

const summary = computed(() => {
    if (query.value.trim() !== '') {
        const count = results.value.length;

        return `${plural(count, 'group matches', 'groups match')} “${query.value.trim()}”${position.value ? ', nearest first' : ''}.`;
    }

    if (position.value) {
        return 'Sorted by distance from you.';
    }

    return `${plural(props.chapters.length, 'group', 'groups')} in ${plural(countryCount.value, 'country', 'countries')}.`;
});

watch([query, position], () => {
    visibleCount.value = PAGE_SIZE;
});

const memberLabel = (count: number): string =>
    `${count} ${count === 1 ? 'member' : 'members'}`;
</script>

<template>
    <Head title="Find a group" />

    <section class="mx-auto w-full max-w-6xl px-6 py-20 md:px-10 md:py-28">
        <p
            class="font-display text-ink-soft text-xs font-semibold tracking-[0.18em] uppercase"
        >
            Group directory
        </p>
        <h1
            class="font-display mt-4 text-3xl font-extrabold tracking-tight uppercase md:text-5xl"
        >
            {{
                hasGroups ? 'Find your closest group' : 'Start the first group'
            }}
        </h1>
        <p class="text-ink-soft mt-6 max-w-2xl text-lg leading-relaxed">
            Groups are a few local photographers who walk, shoot and edit
            together.
            <template v-if="hasGroups">
                Share your location to see the nearest ones, or search for a
                city.
            </template>
            <template v-else>
                There are none yet, so this is your chance to start one where
                you live. We review every group and help you get it going.
            </template>
        </p>

        <Link
            v-if="!hasGroups"
            :href="create()"
            class="bg-ink text-paper font-display hover:bg-ink/85 mt-12 inline-flex items-center gap-2 px-8 py-4 text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
        >
            Start a group
            <ArrowRight class="size-4" />
        </Link>

        <template v-else>
            <div
                class="mt-12 flex flex-col gap-6 md:flex-row md:items-end md:gap-10"
            >
                <div class="flex-1">
                    <label for="group-search" class="sr-only">
                        Search by group, city or country
                    </label>
                    <div
                        class="border-hairline focus-within:border-ink flex items-center gap-3 border-b pb-3"
                    >
                        <Search class="text-ink-soft size-4 shrink-0" />
                        <input
                            id="group-search"
                            v-model="query"
                            type="search"
                            autocomplete="off"
                            placeholder="Search by city, country or group"
                            class="text-ink placeholder:text-ink-soft/60 w-full bg-transparent text-base focus:outline-none"
                        />
                    </div>
                </div>

                <button
                    type="button"
                    class="border-ink/25 font-display text-ink hover:border-ink inline-flex items-center justify-center gap-2 border px-6 py-4 text-xs font-semibold tracking-[0.14em] uppercase transition-colors disabled:opacity-60"
                    :disabled="locating"
                    @click="findNearest"
                >
                    <LoaderCircle v-if="locating" class="size-4 animate-spin" />
                    <LocateFixed v-else class="size-4" />
                    {{ locating ? 'Finding you…' : 'Use my location' }}
                </button>
            </div>

            <p class="text-ink-soft mt-6 text-sm" aria-live="polite">
                <span v-if="locationError" class="text-ink">
                    {{ locationError }}
                </span>
                <span v-else>{{ summary }}</span>
            </p>
        </template>
    </section>

    <template v-if="hasGroups">
        <section class="mx-auto w-full max-w-6xl px-6 pb-16 md:px-10">
            <div class="border-hairline h-[26rem] border md:h-[34rem]">
                <ChapterMap
                    :chapters="results"
                    :position="position"
                    label="Map of StreetWatchers groups"
                />
            </div>
        </section>

        <section class="border-hairline border-t">
            <div class="mx-auto w-full max-w-6xl px-6 py-16 md:px-10 md:py-20">
                <article
                    v-if="closest"
                    class="border-ink mb-14 grid gap-6 border-2 p-8 md:grid-cols-[1fr_auto] md:items-end md:p-10"
                >
                    <div>
                        <p
                            class="font-display text-xs font-semibold tracking-[0.18em] uppercase"
                        >
                            Closest to you
                        </p>
                        <h2
                            class="font-display mt-4 text-2xl font-extrabold tracking-tight uppercase md:text-3xl"
                        >
                            {{ closest.name }}
                        </h2>
                        <p class="text-ink-soft mt-2">
                            {{ closest.city }}, {{ closest.country }} ·
                            {{ memberLabel(closest.members_count) }}
                        </p>
                        <p class="mt-6 max-w-2xl leading-relaxed">
                            {{ closest.description }}
                        </p>
                        <Link
                            :href="show(closest.slug)"
                            class="font-display border-ink text-ink mt-8 inline-flex items-center gap-2 border-b-2 pb-1 text-xs font-semibold tracking-[0.14em] uppercase"
                        >
                            View group
                            <ArrowRight class="size-4" />
                        </Link>
                    </div>
                    <p
                        v-if="closest.distance !== null"
                        class="font-display text-lg font-semibold tabular-nums md:text-right"
                    >
                        {{ formatDistance(closest.distance) }}
                    </p>
                </article>

                <ul
                    v-if="listed.length"
                    class="grid grid-cols-1 gap-px sm:grid-cols-2 lg:grid-cols-3"
                >
                    <li
                        v-for="group in listed"
                        :key="group.id"
                        class="border-hairline -mt-px -ml-px border"
                    >
                        <Link
                            :href="show(group.slug)"
                            class="hover:bg-ink/[0.03] focus-visible:outline-ink flex h-full flex-col p-6 transition-colors focus-visible:outline-2 focus-visible:-outline-offset-2"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <h3
                                    class="font-display text-sm font-extrabold tracking-[0.06em] uppercase"
                                >
                                    {{ group.name }}
                                </h3>
                                <span
                                    v-if="group.distance !== null"
                                    class="text-ink-soft shrink-0 text-xs tabular-nums"
                                >
                                    {{ formatDistance(group.distance) }}
                                </span>
                            </div>
                            <p class="text-ink-soft mt-1 text-sm">
                                {{ group.city }}, {{ group.country }}
                            </p>
                            <p class="mt-4 flex-1 text-sm leading-relaxed">
                                {{ group.description }}
                            </p>
                            <p class="text-ink-soft mt-4 text-xs">
                                {{ memberLabel(group.members_count) }}
                            </p>
                        </Link>
                    </li>
                </ul>

                <div v-else-if="!closest" class="py-10 text-center">
                    <p class="text-lg">
                        No groups match “{{ query.trim() }}” yet.
                    </p>
                    <p class="text-ink-soft mt-2">
                        Try a nearby city, or start the first group there.
                    </p>
                </div>

                <p v-if="remaining > 0" class="mt-12 text-center">
                    <button
                        type="button"
                        class="border-ink/25 font-display text-ink hover:border-ink border px-8 py-4 text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
                        @click="visibleCount += PAGE_SIZE"
                    >
                        Show more groups ({{ remaining }})
                    </button>
                </p>
            </div>
        </section>

        <section class="border-hairline border-t">
            <div
                class="mx-auto flex w-full max-w-6xl flex-col items-start gap-6 px-6 py-16 md:flex-row md:items-center md:justify-between md:px-10"
            >
                <div>
                    <h2
                        class="font-display text-xl font-extrabold tracking-tight uppercase"
                    >
                        No group near you?
                    </h2>
                    <p class="text-ink-soft mt-2">
                        Gather a few photographers and start one. We will help
                        you get it going.
                    </p>
                </div>
                <Link
                    :href="create()"
                    class="font-display border-ink text-ink inline-flex items-center gap-2 border-b-2 pb-1 text-xs font-semibold tracking-[0.14em] uppercase"
                >
                    Start a group
                    <ArrowRight class="size-4" />
                </Link>
            </div>
        </section>
    </template>
</template>
