<script setup lang="ts">
import { ref, watch } from 'vue';

/**
 * A street photograph in a fixed-ratio frame, or a clearly marked stand-in when no `src` is given.
 * Real images and placeholders share the same wrapper so the surrounding whitespace stays
 * consistent. The placeholder fill is solid black so the page carries the weight a contrasty
 * frame will carry.
 *
 * `framed` draws the logo's viewfinder brackets just outside the corners. `priority` loads the
 * image straight away, for photos visible when the page first appears. If the image fails to load,
 * the placeholder is shown instead.
 */
const {
    ratio = 'aspect-[4/5]',
    label = 'Placeholder photograph',
    framed = false,
    src,
    alt = '',
    priority = false,
} = defineProps<{
    ratio?: string;
    label?: string;
    framed?: boolean;
    src?: string;
    alt?: string;
    priority?: boolean;
}>();

const failed = ref(false);

watch(
    () => src,
    () => {
        failed.value = false;
    },
);

/** Corner brackets sit just outside the frame, as they do around the mark. */
const bracketCorners = [
    '-top-2 -left-2 border-t-2 border-l-2 md:-top-3 md:-left-3',
    '-top-2 -right-2 border-t-2 border-r-2 md:-top-3 md:-right-3',
    '-bottom-2 -left-2 border-b-2 border-l-2 md:-bottom-3 md:-left-3',
    '-bottom-2 -right-2 border-b-2 border-r-2 md:-bottom-3 md:-right-3',
];
</script>

<template>
    <div class="relative">
        <img
            v-if="src && !failed"
            :src="src"
            :alt="alt"
            :loading="priority ? 'eager' : 'lazy'"
            :fetchpriority="priority ? 'high' : 'auto'"
            decoding="async"
            :class="[ratio, 'bg-ink block w-full object-cover object-center']"
            @error="failed = true"
        />
        <div
            v-else
            :class="[ratio, 'bg-ink flex w-full items-center justify-center']"
        >
            <span
                class="font-display text-paper/45 px-6 text-center text-[0.6875rem] font-medium tracking-[0.18em] uppercase"
            >
                {{ label }}
            </span>
        </div>

        <template v-if="framed">
            <span
                v-for="corner in bracketCorners"
                :key="corner"
                aria-hidden="true"
                :class="[
                    corner,
                    'border-ink pointer-events-none absolute size-5 md:size-8',
                ]"
            />
        </template>
    </div>
</template>
