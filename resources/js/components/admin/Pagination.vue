<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

defineProps<{
    paginator: Paginated<unknown>;
}>();

/** Laravel labels the first and last links with HTML entities such as "&laquo; Previous". */
function cleanLabel(label: string): string {
    return label.replace('&laquo;', '‹').replace('&raquo;', '›');
}
</script>

<template>
    <nav
        v-if="paginator.last_page > 1"
        class="flex flex-col items-center justify-between gap-3 sm:flex-row"
        aria-label="Pagination"
    >
        <p class="text-muted-foreground text-sm">
            Showing {{ paginator.from }}–{{ paginator.to }} of
            {{ paginator.total }}
        </p>
        <div class="flex flex-wrap gap-1">
            <template v-for="(link, index) in paginator.links" :key="index">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    :class="
                        cn(
                            'inline-flex h-8 min-w-8 items-center justify-center rounded-md border px-2 text-sm',
                            link.active
                                ? 'bg-primary text-primary-foreground border-primary'
                                : 'hover:bg-accent',
                        )
                    "
                    :aria-current="link.active ? 'page' : undefined"
                >
                    {{ cleanLabel(link.label) }}
                </Link>
                <span
                    v-else
                    class="text-muted-foreground inline-flex h-8 min-w-8 items-center justify-center px-2 text-sm"
                >
                    {{ cleanLabel(link.label) }}
                </span>
            </template>
        </div>
    </nav>
</template>
