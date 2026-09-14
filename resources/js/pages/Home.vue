<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ChapterFinder from '@/components/marketing/ChapterFinder.vue';
import ClosingCta from '@/components/marketing/ClosingCta.vue';
import FeaturedGrid from '@/components/marketing/FeaturedGrid.vue';
import HeroSection from '@/components/marketing/HeroSection.vue';
import IntroStatement from '@/components/marketing/IntroStatement.vue';
import type { ChapterDirectoryStats, FeaturedPhoto } from '@/types';

/**
 * Placeholder content. Each value is shaped like the Inertia prop that will
 * replace it, so the sections stay untouched when the backend lands.
 */
const mission =
    'A global community for candid, unstaged photographs of everyday public life.';

const introStatement =
    'A global home for photographers who find the extraordinary in everyday streets.';

const featuredPhotos: FeaturedPhoto[] = [
    {
        id: 1,
        caption: 'Placeholder caption — crossing at first light',
        location: 'Lisbon, Portugal',
        credit: 'Photographer Name',
    },
    {
        id: 2,
        caption: 'Placeholder caption — the last seat on the platform',
        location: 'Osaka, Japan',
        credit: 'Photographer Name',
    },
    {
        id: 3,
        caption: 'Placeholder caption — rain on the market awning',
        location: 'Mexico City, Mexico',
        credit: 'Photographer Name',
    },
    {
        id: 4,
        caption: 'Placeholder caption — two shadows, one wall',
        location: 'Marrakesh, Morocco',
        credit: 'Photographer Name',
    },
    {
        id: 5,
        caption: 'Placeholder caption — waiting out the parade',
        location: 'Chicago, United States',
        credit: 'Photographer Name',
    },
    {
        id: 6,
        caption: 'Placeholder caption — a window, briefly open',
        location: 'Hanoi, Vietnam',
        credit: 'Photographer Name',
    },
];

const chapterStats: ChapterDirectoryStats = {
    chapterCount: 64,
    countryCount: 29,
};
</script>

<template>
    <Head title="Street photography, worldwide" />

    <div>
        <HeroSection :mission="mission" />
        <IntroStatement :statement="introStatement" />
        <FeaturedGrid :photos="featuredPhotos" />
        <ChapterFinder :stats="chapterStats" />
        <ClosingCta />
    </div>
</template>
