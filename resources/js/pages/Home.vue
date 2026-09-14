<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ChapterFinder from '@/components/marketing/ChapterFinder.vue';
import ClosingCta from '@/components/marketing/ClosingCta.vue';
import FeaturedGrid from '@/components/marketing/FeaturedGrid.vue';
import HeroSection from '@/components/marketing/HeroSection.vue';
import IntroStatement from '@/components/marketing/IntroStatement.vue';
import type { ChapterDirectoryStats, FeaturedPhoto } from '@/types';

defineProps<{
    chapterStats: ChapterDirectoryStats;
}>();

/**
 * Placeholder content, apart from the featured photos. Each value is shaped like the Inertia prop that will
 * replace it, so the sections stay untouched when the backend lands.
 */
const mission =
    'A global community for candid, unstaged photographs of everyday public life.';

const introStatement =
    'A global home for photographers who find the extraordinary in everyday streets.';

const featuredPhotos: FeaturedPhoto[] = [
    {
        id: 1,
        src: '/images/_DSC4532.webp',
        alt: 'Black-and-white photograph of a river water taxi named Giddy Up passing into the shadow of a bridge, passengers waving from the deck.',
        caption: 'Giddy up, under the bridge',
        location: 'Chicago, United States',
    },
    {
        id: 2,
        src: '/images/_DSC4545.webp',
        alt: 'Colour photograph of a pedicab driver with his bike pausing beside a man dressed all in white on a sunny downtown street lined with towers.',
        caption: 'A pause between fares',
        location: 'Chicago, United States',
    },
    {
        id: 3,
        src: '/images/_DSC4576.webp',
        alt: 'Black-and-white photograph of an elderly street vendor pushing a sticker-covered ice cream cart past a grand library building.',
        caption: 'The ice cream man on his rounds',
        location: 'Chicago, United States',
    },
    {
        id: 4,
        src: '/images/_DSC4676.webp',
        alt: 'Colour photograph of a quiet intersection beneath green elevated train tracks, a lone pedestrian walking under the steel.',
        caption: 'Under the L',
        location: 'Chicago, United States',
    },
    {
        id: 5,
        src: '/images/_DSC4541.webp',
        alt: 'Photograph of wide stone steps in hard light, a man checking his phone above while figures sit silhouetted in shadow below.',
        caption: 'Light on the steps',
        location: 'Chicago, United States',
    },
    {
        id: 6,
        src: '/images/_DSC4538.webp',
        alt: 'Colour photograph of two pigeons perched on a concrete ledge beneath rusty pipes, with blurred city colour behind them.',
        caption: 'Two regulars on the ledge',
        location: 'Chicago, United States',
    },
];
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
