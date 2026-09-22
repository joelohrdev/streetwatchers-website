<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { store } from '@/actions/App/Http/Controllers/EventController';
import MeetupForm from '@/components/marketing/MeetupForm.vue';
import { show as showGroup } from '@/routes/chapters';

defineProps<{
    group: { name: string; slug: string };
}>();
</script>

<template>
    <Head title="Plan a meetup" />

    <section class="mx-auto w-full max-w-2xl px-6 py-20 md:px-10 md:py-28">
        <Link
            :href="showGroup(group.slug)"
            class="font-display text-ink-soft hover:text-ink inline-flex items-center gap-2 text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
        >
            <ArrowLeft class="size-4" />
            {{ group.name }}
        </Link>
        <h1
            class="font-display mt-4 text-3xl font-extrabold tracking-tight uppercase md:text-5xl"
        >
            Plan a meetup
        </h1>
        <p class="text-ink-soft mt-6 text-lg leading-relaxed">
            A photo walk or get-together for {{ group.name }}. It is public, so
            people who haven't joined yet can find it too.
        </p>

        <MeetupForm
            :action="store.form(group.slug)"
            submit-label="Publish meetup"
        />
    </section>
</template>
