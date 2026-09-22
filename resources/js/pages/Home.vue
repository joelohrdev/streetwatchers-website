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

const mission =
    'Find a street photography group in your city and join its next photo walk.';

const introStatement =
    'Photo walks run by local photographers, open to anyone with a camera or a phone.';
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
