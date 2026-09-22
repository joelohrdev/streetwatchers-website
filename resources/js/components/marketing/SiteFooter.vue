<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { AtSign, Camera, Rss } from '@lucide/vue';
import { computed } from 'vue';
import SiteLogo from '@/components/marketing/SiteLogo.vue';
import { index as chapterDirectory } from '@/routes/chapters';
import { index as collectiveDirectory } from '@/routes/collectives';
import { create as contact } from '@/routes/contact-messages';
import { create as photoRemovalRequest } from '@/routes/photo-removal-requests';

const page = usePage();

/** Placeholder destinations until the supporting pages exist. Collectives are left out until they launch. */
const footerLinks = computed(() => [
    { label: 'Groups', href: chapterDirectory.url() },
    ...(page.props.features.collectives
        ? [{ label: 'Collectives', href: collectiveDirectory.url() }]
        : []),
    { label: 'Code of Conduct', href: '#' },
    { label: 'Privacy', href: '#' },
    { label: 'Contact', href: contact.url() },
    { label: 'Request photo removal', href: photoRemovalRequest.url() },
]);

/** Generic stand-ins: lucide no longer ships brand marks. */
const socialLinks = [
    { label: 'Instagram', href: '#', icon: Camera },
    { label: 'Mastodon', href: '#', icon: AtSign },
    { label: 'Journal feed', href: '#', icon: Rss },
];
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
                class="border-hairline flex items-center justify-between gap-8 border-t pt-10"
            >
                <p class="text-ink-soft/80 text-xs">
                    &copy; {{ new Date().getFullYear() }} StreetWatchers
                </p>

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
        </div>
    </footer>
</template>
