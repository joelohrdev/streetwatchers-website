<script setup lang="ts">
/**
 * A member's name, followed by a link to their Instagram when they have shared one.
 */
defineProps<{
    name: string;
    instagramHandle: string | null;
}>();
</script>

<template>
    <span>
        {{ name }}
        <a
            v-if="instagramHandle"
            :href="`https://www.instagram.com/${instagramHandle}/`"
            target="_blank"
            rel="noopener noreferrer"
            class="text-ink-soft hover:text-ink ml-1 text-sm transition-colors"
            :aria-label="`${name} on Instagram`"
            >@{{ instagramHandle }}</a
        >
    </span>
</template>
