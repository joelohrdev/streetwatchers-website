<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ChapterFinder from '@/components/marketing/ChapterFinder.vue';
import ClosingCta from '@/components/marketing/ClosingCta.vue';
import HeroSection from '@/components/marketing/HeroSection.vue';
import IntroStatement from '@/components/marketing/IntroStatement.vue';
import UpcomingMeetups from '@/components/marketing/UpcomingMeetups.vue';
import type { ChapterDirectoryStats, UpcomingMeetup } from '@/types';

defineProps<{
    chapterStats: ChapterDirectoryStats;
    upcomingMeetups: UpcomingMeetup[];
}>();

/**
 * Placeholder content. Each value is shaped like the Inertia prop that will
 * replace it, so the sections stay untouched when the backend lands.
 */
const mission =
    'A global community for candid, unstaged photographs of everyday public life.';

const introStatement =
    'A global home for photographers who find the extraordinary in everyday streets.';
</script>

<template>
    <Head title="Street photography, worldwide" />

    <div>
        <HeroSection :mission="mission" />
        <IntroStatement :statement="introStatement" />
        <UpcomingMeetups :meetups="upcomingMeetups" />
        <ChapterFinder :stats="chapterStats" />
        <ClosingCta />
    </div>
</template>
