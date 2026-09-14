<script setup lang="ts">
import AnnouncementBanner from '@/components/AnnouncementBanner.vue';
import SiteFooter from '@/components/marketing/SiteFooter.vue';
import SiteHeader from '@/components/marketing/SiteHeader.vue';
</script>

<template>
    <div class="bg-paper text-ink flex min-h-svh flex-col">
        <AnnouncementBanner />
        <SiteHeader />
        <main class="flex-1">
            <slot />
        </main>
        <SiteFooter />
    </div>
</template>
