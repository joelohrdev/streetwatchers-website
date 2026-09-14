<script setup lang="ts">
const corners = [
    'top-0 left-0 border-t-2 border-l-2',
    'top-0 right-0 border-t-2 border-r-2',
    'bottom-0 left-0 border-b-2 border-l-2',
    'bottom-0 right-0 border-b-2 border-r-2',
];
</script>

<template>
    <span
        aria-hidden="true"
        class="pointer-events-none absolute -inset-y-1 -right-1 -left-1.5"
    >
        <span
            v-for="corner in corners"
            :key="corner"
            :class="['border-ink absolute size-2', corner]"
        />
    </span>
</template>
