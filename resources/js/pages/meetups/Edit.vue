<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { update } from '@/actions/App/Http/Controllers/EventController';
import type { MeetupDetails } from '@/components/marketing/MeetupForm.vue';
import MeetupForm from '@/components/marketing/MeetupForm.vue';
import { show as showMeetup } from '@/routes/chapters/events';

defineProps<{
    group: { name: string; slug: string };
    meetup: MeetupDetails & { id: number };
}>();
</script>

<template>
    <Head :title="`Edit ${meetup.title}`" />

    <section class="mx-auto w-full max-w-2xl px-6 py-20 md:px-10 md:py-28">
        <Link
            :href="showMeetup([group.slug, meetup.id])"
            class="font-display text-ink-soft hover:text-ink inline-flex items-center gap-2 text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
        >
            <ArrowLeft class="size-4" />
            Back to the meetup
        </Link>
        <h1
            class="font-display mt-4 text-3xl font-extrabold tracking-tight uppercase md:text-5xl"
        >
            Edit meetup
        </h1>

        <MeetupForm
            :action="update.form([group.slug, meetup.id])"
            :meetup="meetup"
            submit-label="Save changes"
        />
    </section>
</template>
