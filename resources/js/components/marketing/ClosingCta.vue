<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import CrosswalkRule from '@/components/marketing/CrosswalkRule.vue';
import { register } from '@/routes';
</script>

<template>
    <section class="bg-ink text-paper">
        <div
            class="mx-auto flex w-full max-w-2xl flex-col items-center px-6 py-28 text-center md:px-10 md:py-36"
        >
            <CrosswalkRule />
            <h2
                class="font-display mt-12 text-2xl font-extrabold tracking-tight text-balance uppercase md:text-5xl"
            >
                Walk with a group near you
            </h2>
            <Link
                :href="register()"
                class="font-display border-paper bg-paper text-ink hover:bg-ink hover:text-paper mt-12 inline-flex border px-8 py-4 text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
            >
                Join for free
            </Link>
        </div>
    </section>
</template>
