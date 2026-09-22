<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { useAnalyticsConsent } from '@/composables/useAnalyticsConsent';
import {
    inlineLinkClass,
    primaryButtonClass,
    secondaryButtonClass,
} from '@/lib/marketing';
import { privacy } from '@/routes';

/**
 * Asks visitors whether they allow Google Analytics. Nothing renders when analytics is switched off, and the
 * banner waits until the page is running in the browser, where the visitor's earlier choice can be read.
 */
const page = usePage();
const { bannerOpen, start, allow, decline } = useAnalyticsConsent();
const ready = ref(false);

onMounted(() => {
    const analytics = page.props.analytics;

    if (analytics) {
        start(analytics.measurementId);
        ready.value = true;
    }
});
</script>

<template>
    <div
        v-if="ready && bannerOpen"
        role="dialog"
        aria-labelledby="analytics-consent-title"
        class="border-ink bg-paper text-ink fixed inset-x-4 bottom-4 z-50 mx-auto max-w-xl border-2 p-6 sm:inset-x-6"
    >
        <p
            id="analytics-consent-title"
            class="font-display text-xs font-semibold tracking-[0.14em] uppercase"
        >
            Allow analytics?
        </p>
        <p class="text-ink-soft mt-3 text-sm leading-relaxed">
            We'd like to use Google Analytics to see which pages people visit,
            so we can improve the site. It sets cookies and shares usage data
            with Google. Read more in our
            <Link :href="privacy()" :class="inlineLinkClass"
                >privacy policy</Link
            >.
        </p>
        <div class="mt-5 flex flex-col gap-3 sm:flex-row">
            <button
                type="button"
                :class="[primaryButtonClass, 'sm:w-auto']"
                @click="allow"
            >
                Allow
            </button>
            <button
                type="button"
                :class="[secondaryButtonClass, 'sm:w-auto']"
                @click="decline"
            >
                No thanks
            </button>
        </div>
    </div>
</template>
