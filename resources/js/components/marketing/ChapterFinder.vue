<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ArrowRight, MapPin } from '@lucide/vue';
import { ref } from 'vue';
import {
    create as startGroup,
    index as chapterDirectory,
} from '@/routes/chapters';
import type { ChapterDirectoryStats } from '@/types';

defineProps<{
    stats: ChapterDirectoryStats;
}>();

const location = ref('');

function findGroup(): void {
    const q = location.value.trim();

    router.visit(chapterDirectory({ query: { q: q === '' ? undefined : q } }));
}

const plural = (count: number, singular: string, pluralForm: string): string =>
    `${count} ${count === 1 ? singular : pluralForm}`;
</script>

<template>
    <section id="groups" class="border-hairline border-t">
        <div
            class="mx-auto w-full max-w-2xl px-6 py-24 text-center md:px-10 md:py-32"
        >
            <h2
                class="font-display text-2xl font-extrabold tracking-tight uppercase md:text-4xl"
            >
                {{
                    stats.chapterCount > 0
                        ? 'Find your group'
                        : 'Start the first group'
                }}
            </h2>
            <p class="text-ink-soft mt-6 text-lg leading-relaxed text-balance">
                Groups are a few local photographers who walk, shoot and edit
                together.
                {{
                    stats.chapterCount > 0
                        ? 'Start with the city you know best.'
                        : 'There are none yet, so start one where you live.'
                }}
            </p>

            <form
                v-if="stats.chapterCount > 0"
                class="mt-14"
                @submit.prevent="findGroup"
            >
                <label for="group-location" class="sr-only">
                    City, region or country
                </label>
                <div
                    class="border-hairline focus-within:border-ink mx-auto flex max-w-md items-center gap-3 border-b pb-3"
                >
                    <MapPin class="text-ink-soft size-4 shrink-0" />
                    <input
                        id="group-location"
                        v-model="location"
                        type="search"
                        placeholder="City, region or country"
                        class="text-ink placeholder:text-ink-soft/60 w-full bg-transparent text-base focus:outline-none"
                    />
                </div>

                <p class="text-ink-soft mt-8 text-sm">
                    {{ plural(stats.chapterCount, 'group', 'groups') }} in
                    {{ plural(stats.countryCount, 'country', 'countries') }}
                </p>

                <button
                    type="submit"
                    class="border-ink/25 font-display text-ink hover:border-ink mt-8 inline-flex border px-8 py-4 text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
                >
                    Find a Group
                </button>
            </form>

            <Link
                v-else
                :href="startGroup()"
                class="bg-ink text-paper font-display hover:bg-ink/85 mt-14 inline-flex items-center gap-2 px-8 py-4 text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
            >
                Start a group
                <ArrowRight class="size-4" />
            </Link>
        </div>
    </section>
</template>
