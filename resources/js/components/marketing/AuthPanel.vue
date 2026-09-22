<script setup lang="ts">
/** The narrow, centered column used by the public site's sign-in and account pages. */
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <section class="mx-auto w-full max-w-md px-6 py-20 md:py-28">
        <p
            class="font-display text-ink-soft text-xs font-semibold tracking-[0.18em] uppercase"
        >
            StreetWatchers
        </p>
        <h1
            class="font-display mt-4 text-3xl font-extrabold tracking-tight uppercase md:text-4xl"
        >
            {{ title }}
        </h1>
        <p
            v-if="description"
            class="text-ink-soft mt-4 text-lg leading-relaxed"
        >
            {{ description }}
        </p>

        <div class="mt-12">
            <slot />
        </div>
    </section>
</template>
