<script setup lang="ts">
import type { InertiaLinkProps } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';

export type FilterTab = {
    label: string;
    href: NonNullable<InertiaLinkProps['href']>;
    active: boolean;
};

defineProps<{
    tabs: FilterTab[];
    label: string;
}>();
</script>

<template>
    <nav
        class="bg-muted inline-flex w-fit flex-wrap gap-1 rounded-lg p-1"
        :aria-label="label"
    >
        <Link
            v-for="tab in tabs"
            :key="tab.label"
            :href="tab.href"
            preserve-scroll
            preserve-state
            :class="
                cn(
                    'rounded-md px-3 py-1 text-sm font-medium transition-colors',
                    tab.active
                        ? 'bg-background text-foreground shadow-xs'
                        : 'text-muted-foreground hover:text-foreground',
                )
            "
            :aria-current="tab.active ? 'page' : undefined"
        >
            {{ tab.label }}
        </Link>
    </nav>
</template>
