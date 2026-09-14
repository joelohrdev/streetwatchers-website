<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
</script>

<template>
    <Head title="Appearance settings" />

    <div>
        <h2
            class="font-display text-lg font-extrabold tracking-tight uppercase"
        >
            Appearance
        </h2>
        <p class="text-ink-soft mt-2 leading-relaxed">
            Choose light, dark or your system theme. This only changes the admin
            panel; the rest of StreetWatchers always stays light.
        </p>

        <AppearanceTabs class="mt-8" />
    </div>
</template>
