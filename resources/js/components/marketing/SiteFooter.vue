<script setup lang="ts">
import { ArrowRight, AtSign, Camera, Rss } from '@lucide/vue';
import { ref } from 'vue';
import SiteLogo from '@/components/marketing/SiteLogo.vue';
import { index as chapterDirectory } from '@/routes/chapters';
import { create as photoRemovalRequest } from '@/routes/photo-removal-requests';

/** Placeholder destinations until the supporting pages exist. */
const footerLinks = [
    { label: 'Groups', href: chapterDirectory.url() },
    { label: 'Collectives', href: '#' },
    { label: 'Code of Conduct', href: '#' },
    { label: 'Privacy', href: '#' },
    { label: 'Request photo removal', href: photoRemovalRequest.url() },
];

/** Generic stand-ins: lucide no longer ships brand marks. */
const socialLinks = [
    { label: 'Instagram', href: '#', icon: Camera },
    { label: 'Mastodon', href: '#', icon: AtSign },
    { label: 'Journal feed', href: '#', icon: Rss },
];

const email = ref('');

/** TODO: post to the newsletter endpoint once it exists. */
function subscribe(): void {
    email.value = '';
}
</script>

<template>
    <footer class="border-hairline border-t">
        <div
            class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-6 py-14 md:px-10"
        >
            <div
                class="flex flex-col gap-8 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <SiteLogo variant="mark" class="h-10" />
                </div>

                <nav class="flex flex-wrap gap-x-8 gap-y-3">
                    <a
                        v-for="link in footerLinks"
                        :key="link.label"
                        :href="link.href"
                        class="text-ink-soft hover:text-ink text-sm transition-colors"
                    >
                        {{ link.label }}
                    </a>
                </nav>
            </div>

            <div
                class="border-hairline flex flex-col gap-8 border-t pt-10 md:flex-row md:items-end md:justify-between"
            >
                <form
                    class="w-full max-w-sm"
                    novalidate
                    @submit.prevent="subscribe"
                >
                    <label
                        for="newsletter-email"
                        class="text-ink-soft text-xs tracking-[0.14em] uppercase"
                    >
                        The weekly frame
                    </label>
                    <div
                        class="border-hairline focus-within:border-ink mt-3 flex items-center gap-3 border-b pb-2"
                    >
                        <input
                            id="newsletter-email"
                            v-model="email"
                            type="email"
                            placeholder="you@example.com"
                            class="text-ink placeholder:text-ink-soft/60 w-full bg-transparent text-sm focus:outline-none"
                        />
                        <button
                            type="submit"
                            class="text-ink transition-opacity hover:opacity-60"
                            aria-label="Subscribe to the newsletter"
                        >
                            <ArrowRight class="size-4" />
                        </button>
                    </div>
                </form>

                <div class="flex items-center gap-6">
                    <a
                        v-for="social in socialLinks"
                        :key="social.label"
                        :href="social.href"
                        :aria-label="social.label"
                        class="text-ink-soft hover:text-ink transition-colors"
                    >
                        <component :is="social.icon" class="size-4" />
                    </a>
                </div>
            </div>

            <p class="text-ink-soft/80 text-xs">
                &copy; {{ new Date().getFullYear() }} StreetWatchers
            </p>
        </div>
    </footer>
</template>
