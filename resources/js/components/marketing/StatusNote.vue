<script setup lang="ts">
/** A short confirmation, such as "We have emailed your password reset link." */
</script>

<template>
    <p
        role="status"
        class="border-ink text-ink mb-10 border-l-2 py-1 pl-4 text-sm leading-relaxed"
    >
        <slot />
    </p>
</template>
