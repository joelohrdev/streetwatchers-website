<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { store } from '@/actions/App/Http/Controllers/ChapterController';
import GroupForm from '@/components/marketing/GroupForm.vue';
import { primaryButtonClass } from '@/lib/marketing';
import { dashboard } from '@/routes';

defineProps<{
    submittedChapter: string | null;
    countries: { value: string; label: string }[];
}>();

const nextSteps = [
    "You are the group's organizer, so you will be able to plan its meetups once it's live.",
    'Our team reviews the group and it appears in the directory once it is approved.',
    'Once it is live, you can make other members organizers to share the job.',
];
</script>

<template>
    <Head title="Start a group" />

    <section class="mx-auto w-full max-w-2xl px-6 py-20 md:px-10 md:py-28">
        <p
            class="font-display text-ink-soft text-xs font-semibold tracking-[0.18em] uppercase"
        >
            Groups
        </p>

        <template v-if="submittedChapter">
            <h1
                class="font-display mt-4 text-3xl font-extrabold tracking-tight uppercase md:text-4xl"
            >
                {{ submittedChapter }} has been submitted
            </h1>
            <p class="text-ink-soft mt-6 text-lg leading-relaxed">
                Thanks for starting a group. Here is what happens next.
            </p>

            <ol class="border-hairline mt-12 border-t">
                <li
                    v-for="(step, index) in nextSteps"
                    :key="index"
                    class="border-hairline flex gap-6 border-b py-6"
                >
                    <span
                        class="font-display shrink-0 text-sm font-extrabold tabular-nums"
                    >
                        {{ String(index + 1).padStart(2, '0') }}
                    </span>
                    <span class="leading-relaxed">{{ step }}</span>
                </li>
            </ol>

            <div class="mt-12 sm:max-w-xs">
                <Link :href="dashboard()" :class="primaryButtonClass">
                    Back to your memberships
                </Link>
            </div>
        </template>

        <template v-else>
            <h1
                class="font-display mt-4 text-3xl font-extrabold tracking-tight uppercase md:text-5xl"
            >
                Start a group
            </h1>
            <p class="text-ink-soft mt-6 text-lg leading-relaxed">
                Groups are a few local photographers who walk, shoot and edit
                together. Tell us about yours and we will review it.
            </p>

            <GroupForm
                :action="store.form()"
                :countries="countries"
                submit-label="Submit for approval"
            />
        </template>
    </section>
</template>
