<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    status: string;
    label?: string;
}>();

/** Status values share colors across chapters, reports, photos, users and groups. */
const tones: Record<string, string> = {
    pending:
        'bg-amber-100 text-amber-900 dark:bg-amber-500/15 dark:text-amber-300',
    open: 'bg-amber-100 text-amber-900 dark:bg-amber-500/15 dark:text-amber-300',
    forming:
        'bg-amber-100 text-amber-900 dark:bg-amber-500/15 dark:text-amber-300',
    active: 'bg-emerald-100 text-emerald-900 dark:bg-emerald-500/15 dark:text-emerald-300',
    published:
        'bg-emerald-100 text-emerald-900 dark:bg-emerald-500/15 dark:text-emerald-300',
    actioned:
        'bg-emerald-100 text-emerald-900 dark:bg-emerald-500/15 dark:text-emerald-300',
    verified: 'bg-sky-100 text-sky-900 dark:bg-sky-500/15 dark:text-sky-300',
    reviewed: 'bg-sky-100 text-sky-900 dark:bg-sky-500/15 dark:text-sky-300',
    flagged:
        'bg-orange-100 text-orange-900 dark:bg-orange-500/15 dark:text-orange-300',
    suspended:
        'bg-orange-100 text-orange-900 dark:bg-orange-500/15 dark:text-orange-300',
    removed: 'bg-red-100 text-red-900 dark:bg-red-500/15 dark:text-red-300',
    banned: 'bg-red-100 text-red-900 dark:bg-red-500/15 dark:text-red-300',
};

const neutral =
    'bg-neutral-100 text-neutral-700 dark:bg-neutral-500/15 dark:text-neutral-300';

const text = computed(
    () =>
        props.label ??
        props.status.charAt(0).toUpperCase() +
            props.status.slice(1).replaceAll('_', ' '),
);
</script>

<template>
    <span
        :class="
            cn(
                'inline-flex w-fit items-center rounded-full px-2 py-0.5 text-xs font-medium whitespace-nowrap',
                tones[status] ?? neutral,
            )
        "
    >
        {{ text }}
    </span>
</template>
