<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';

const { isCurrentOrParentUrl } = useCurrentUrl();

const tabs = [
    { title: 'Profile', href: editProfile() },
    { title: 'Password', href: editSecurity() },
];
</script>

<template>
    <section class="mx-auto w-full max-w-3xl px-6 py-20 md:px-10 md:py-28">
        <Link
            :href="dashboard()"
            class="font-display text-ink-soft hover:text-ink inline-flex items-center gap-2 text-xs font-semibold tracking-[0.14em] uppercase transition-colors"
        >
            <ArrowLeft class="size-4" />
            Back to your memberships
        </Link>

        <p
            class="font-display text-ink-soft mt-12 text-xs font-semibold tracking-[0.18em] uppercase"
        >
            Your account
        </p>
        <h1
            class="font-display mt-4 text-3xl font-extrabold tracking-tight uppercase md:text-4xl"
        >
            Settings
        </h1>

        <nav
            class="border-hairline mt-10 flex flex-wrap gap-x-8 gap-y-3 border-b"
            aria-label="Settings"
        >
            <Link
                v-for="tab in tabs"
                :key="tab.title"
                :href="tab.href"
                :aria-current="
                    isCurrentOrParentUrl(tab.href) ? 'page' : undefined
                "
                :class="
                    cn(
                        'font-display -mb-px border-b-2 pb-3 text-xs font-semibold tracking-[0.14em] uppercase transition-colors',
                        isCurrentOrParentUrl(tab.href)
                            ? 'border-ink text-ink'
                            : 'text-ink-soft hover:text-ink border-transparent',
                    )
                "
            >
                {{ tab.title }}
            </Link>
        </nav>

        <div class="mt-12 flex flex-col gap-16">
            <slot />
        </div>
    </section>
</template>
