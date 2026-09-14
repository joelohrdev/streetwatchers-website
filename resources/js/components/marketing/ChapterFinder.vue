<script setup lang="ts">
import { MapPin } from '@lucide/vue';
import { ref } from 'vue';
import type { ChapterDirectoryStats } from '@/types';

defineProps<{
    stats: ChapterDirectoryStats;
}>();

const location = ref('');

/** TODO: visit the chapter directory with this query once that route exists. */
function findChapter(): void {
    //
}
</script>

<template>
    <section id="chapters" class="border-hairline border-t">
        <div
            class="mx-auto w-full max-w-2xl px-6 py-24 text-center md:px-10 md:py-32"
        >
            <h2
                class="font-display text-2xl font-extrabold tracking-tight uppercase md:text-4xl"
            >
                Find your chapter
            </h2>
            <p class="text-ink-soft mt-6 text-lg leading-relaxed text-balance">
                Chapters are small local groups who walk, shoot and edit
                together. Start with the city you know best.
            </p>

            <form class="mt-14" @submit.prevent="findChapter">
                <label for="chapter-location" class="sr-only">
                    City, region or country
                </label>
                <div
                    class="border-hairline focus-within:border-ink mx-auto flex max-w-md items-center gap-3 border-b pb-3"
                >
                    <MapPin class="text-ink-soft size-4 shrink-0" />
                    <input
                        id="chapter-location"
                        v-model="location"
                        type="search"
                        placeholder="City, region or country"
                        class="text-ink placeholder:text-ink-soft/60 w-full bg-transparent text-base focus:outline-none"
                    />
                </div>

                <p class="text-ink-soft mt-8 text-sm">
                    {{ stats.chapterCount }} chapters in
                    {{ stats.countryCount }} countries
                </p>

                <button
                    type="submit"
                    class="border-ink/25 font-display text-ink hover:border-ink mt-8 inline-flex border px-8 py-4 text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
                >
                    Find a Chapter
                </button>
            </form>
        </div>
    </section>
</template>
