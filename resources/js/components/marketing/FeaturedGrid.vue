<script setup lang="ts">
import { ArrowRight } from '@lucide/vue';
import PlaceholderPhoto from '@/components/marketing/PlaceholderPhoto.vue';
import type { FeaturedPhoto } from '@/types';

defineProps<{
    photos: FeaturedPhoto[];
}>();
</script>

<template>
    <section id="featured" class="border-hairline border-t">
        <div class="mx-auto w-full max-w-6xl px-6 py-24 md:px-10 md:py-32">
            <p
                class="font-display text-ink-soft text-xs font-semibold tracking-[0.18em] uppercase"
            >
                Selected frames
            </p>

            <div
                class="mt-14 grid grid-cols-1 gap-x-10 gap-y-14 sm:grid-cols-2 md:gap-x-14 md:gap-y-20 lg:grid-cols-3"
            >
                <figure
                    v-for="photo in photos"
                    :key="photo.id"
                    class="group"
                    tabindex="0"
                >
                    <PlaceholderPhoto
                        ratio="aspect-[4/5]"
                        :label="`Placeholder — ${photo.location}`"
                    />
                    <figcaption
                        class="text-ink-soft mt-4 text-sm transition-opacity duration-300 md:opacity-0 md:group-focus-within:opacity-100 md:group-hover:opacity-100"
                    >
                        <span class="text-ink">{{ photo.caption }}</span>
                        <span class="text-ink-soft/80 block">
                            {{ photo.location }} — {{ photo.credit }}
                        </span>
                    </figcaption>
                </figure>
            </div>

            <p class="mt-20 text-center">
                <!-- TODO: point at the feed route once it exists. -->
                <a
                    href="#"
                    class="font-display border-ink text-ink inline-flex items-center gap-2 border-b-2 pb-1 text-xs font-semibold tracking-[0.14em] uppercase"
                >
                    Explore the Feed
                    <ArrowRight class="size-4" />
                </a>
            </p>
        </div>
    </section>
</template>
