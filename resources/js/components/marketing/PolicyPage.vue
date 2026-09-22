<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

/**
 * A long-form policy page, such as the privacy policy. Headings, paragraphs, lists and links placed in the
 * default slot are styled here, so each policy page only needs plain markup.
 */
defineProps<{
    title: string;
    updated: string;
}>();
</script>

<template>
    <Head :title="title" />

    <article class="mx-auto w-full max-w-2xl px-6 py-20 md:px-10 md:py-28">
        <p
            class="font-display text-ink-soft text-xs font-semibold tracking-[0.18em] uppercase"
        >
            Last updated {{ updated }}
        </p>
        <h1
            class="font-display mt-4 text-3xl font-extrabold tracking-tight uppercase md:text-5xl"
        >
            {{ title }}
        </h1>

        <div
            class="[&_a]:text-ink [&_a]:border-ink [&_h2]:font-display [&_h2]:border-hairline [&_li]:text-ink-soft [&_p]:text-ink-soft [&_strong]:text-ink mt-12 leading-relaxed [&_a]:border-b [&_h2]:mt-14 [&_h2]:border-t [&_h2]:pt-10 [&_h2]:text-xs [&_h2]:font-semibold [&_h2]:tracking-[0.18em] [&_h2]:uppercase [&_li]:mt-2 [&_p]:mt-5 [&_strong]:font-semibold [&_ul]:mt-5 [&_ul]:list-disc [&_ul]:pl-5 [&_code]:font-mono [&_code]:text-[0.9em]"
        >
            <slot />
        </div>
    </article>
</template>
