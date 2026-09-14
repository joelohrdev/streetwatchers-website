<script setup lang="ts">
import { formatDateTime } from '@/lib/utils';
import type { TimelineEntry } from '@/types';

defineProps<{
    entries: TimelineEntry[];
}>();

function humanize(value: string): string {
    return value.replaceAll('_', ' ');
}
</script>

<template>
    <p v-if="entries.length === 0" class="text-muted-foreground text-sm">
        No admin actions recorded yet.
    </p>
    <ol v-else class="border-border space-y-5 border-l pl-5">
        <li v-for="entry in entries" :key="entry.id" class="relative">
            <span
                class="bg-border absolute top-1.5 -left-[25px] size-2.5 rounded-full"
                aria-hidden="true"
            />
            <p class="text-sm font-medium">
                {{ entry.action }}
                <span
                    v-if="entry.old_status || entry.new_status"
                    class="text-muted-foreground font-normal"
                >
                    · {{ humanize(entry.old_status ?? 'none') }} →
                    {{ humanize(entry.new_status ?? 'none') }}
                </span>
            </p>
            <p class="text-muted-foreground text-xs">
                {{ entry.actor ?? 'Deleted user' }} ·
                <time :datetime="entry.created_at">
                    {{ formatDateTime(entry.created_at) }}
                </time>
            </p>
            <p
                v-if="entry.reason"
                class="bg-muted mt-2 rounded-md px-3 py-2 text-sm whitespace-pre-line"
            >
                {{ entry.reason }}
            </p>
        </li>
    </ol>
</template>
