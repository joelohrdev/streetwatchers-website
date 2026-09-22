<script setup lang="ts">
/**
 * The crosswalk receding to a vanishing point inside the logo mark, reduced to
 * a centered ornament. Takes its color from the surrounding text so it
 * inverts on the black band. Purely decorative.
 */
const bars = [
    'h-[3px] w-16',
    'h-[3px] w-12',
    'h-0.5 w-9',
    'h-0.5 w-6',
    'h-px w-4',
];
</script>

<template>
    <div
        aria-hidden="true"
        class="flex flex-col-reverse items-center gap-[5px]"
    >
        <span
            v-for="bar in bars"
            :key="bar"
            :class="[bar, 'block bg-current']"
        />
    </div>
</template>
