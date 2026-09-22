<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import SiteLogo from '@/components/marketing/SiteLogo.vue';
import { useAnalyticsConsent } from '@/composables/useAnalyticsConsent';
import { codeOfConduct, privacy } from '@/routes';
import { index as chapterDirectory } from '@/routes/chapters';
import { index as collectiveDirectory } from '@/routes/collectives';
import { create as contact } from '@/routes/contact-messages';

const page = usePage();
const { reopen: reopenConsent } = useAnalyticsConsent();

/** Collectives and photo removal requests are left out until collectives and photos launch. */
const footerLinks = computed(() => [
    { label: 'Groups', href: chapterDirectory.url() },
    ...(page.props.features.collectives
        ? [{ label: 'Collectives', href: collectiveDirectory.url() }]
        : []),
    { label: 'Code of Conduct', href: codeOfConduct.url() },
    { label: 'Privacy', href: privacy.url() },
    { label: 'Contact', href: contact.url() },
]);
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
                    <button
                        v-if="page.props.analytics"
                        type="button"
                        class="text-ink-soft hover:text-ink cursor-pointer text-sm transition-colors"
                        @click="reopenConsent"
                    >
                        Cookie settings
                    </button>
                </nav>
            </div>

            <div class="border-hairline border-t pt-10">
                <p class="text-ink-soft/80 text-xs">
                    &copy; {{ new Date().getFullYear() }} StreetWatchers
                </p>
            </div>
        </div>
    </footer>
</template>
