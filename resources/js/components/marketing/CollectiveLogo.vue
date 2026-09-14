<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

/** A collective's logo, or a monogram of its initials when it has none. */
const {
    name,
    logoUrl,
    class: className,
} = defineProps<{
    name: string;
    logoUrl: string | null;
    class?: string;
}>();

const initials = computed(() =>
    name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word.charAt(0).toUpperCase())
        .join(''),
);
</script>

<template>
    <img
        v-if="logoUrl"
        :src="logoUrl"
        :alt="`${name} logo`"
        :class="cn('bg-paper aspect-square object-cover', className)"
    />
    <span
        v-else
        aria-hidden="true"
        :class="
            cn(
                'bg-ink text-paper font-display flex aspect-square items-center justify-center font-extrabold tracking-tight',
                className,
            )
        "
    >
        {{ initials }}
    </span>
</template>
