<script setup lang="ts">
import AnnouncementBanner from '@/components/AnnouncementBanner.vue';
import AnalyticsConsent from '@/components/marketing/AnalyticsConsent.vue';
import SiteFooter from '@/components/marketing/SiteFooter.vue';
import SiteHeader from '@/components/marketing/SiteHeader.vue';
import { Toaster } from '@/components/ui/sonner';
</script>

<template>
    <div class="bg-paper text-ink flex min-h-svh flex-col">
        <AnnouncementBanner />
        <SiteHeader />
        <main class="flex-1">
            <slot />
        </main>
        <SiteFooter />
        <!-- Confirmations such as "Profile updated" arrive as flash toasts. The public site is always light. -->
        <Toaster theme="light" position="bottom-center" />
        <AnalyticsConsent />
    </div>
</template>
